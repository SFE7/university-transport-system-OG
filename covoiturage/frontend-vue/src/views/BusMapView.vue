<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import apiClient from '@/lib/apiClient'

const mapEl = ref<HTMLDivElement | null>(null)

const toasts = ref<Array<{ id: number; message: string }>>([])
let toastSeq = 0
let map: L.Map | null = null
const drawnLayers: L.Layer[] = []
const busMarkers: L.Layer[] = []
let busRefreshTimer: number | null = null

const showToast = (message: string) => {
  const id = ++toastSeq
  toasts.value.unshift({ id, message })
  window.setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 5000)
}

async function drawRoutedLine(
  mapInstance: L.Map,
  arrets: Array<{ latitude: string | number; longitude: string | number }>,
  color: string,
  bounds: L.LatLngExpression[]
): Promise<L.Polyline | null> {
  const coords = arrets
    .map((a) => `${parseFloat(String(a.longitude))},${parseFloat(String(a.latitude))}`)
    .join(';')

  const fallback = arrets
    .map((a) => [parseFloat(String(a.latitude)), parseFloat(String(a.longitude))] as [number, number])
    .filter((coord) => coord.every((value) => Number.isFinite(value)))

  try {
    const url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`
    const res = await fetch(url)
    const data: {
      routes?: Array<{
        geometry?: {
          coordinates?: number[][]
        }
      }>
    } = await res.json()

    if (data.routes && data.routes[0]) {
      const routeCoords = (data.routes[0].geometry?.coordinates || []).map(
        (c: number[]) => [c[1], c[0]] as [number, number]
      )
      routeCoords.forEach((c) => bounds.push(c))
      const polyline = L.polyline(routeCoords, { color, weight: 5, opacity: 0.9 }).addTo(mapInstance)
      drawnLayers.push(polyline)
      return polyline
    }

    fallback.forEach((c) => bounds.push(c))
    const polyline = L.polyline(fallback, { color, weight: 5, opacity: 0.9 }).addTo(mapInstance)
    drawnLayers.push(polyline)
    return polyline
  } catch {
    fallback.forEach((c) => bounds.push(c))
    const polyline = L.polyline(fallback, { color, weight: 5, opacity: 0.9 }).addTo(mapInstance)
    drawnLayers.push(polyline)
    return polyline
  }
}

const clearBusMarkers = () => {
  if (!map) return
  while (busMarkers.length) {
    const layer = busMarkers.pop()
    if (layer) {
      map.removeLayer(layer)
    }
  }
}

const drawActiveBusMarkers = async () => {
  if (!map) return

  try {
    const response = await apiClient.get('/bus/positions')
    const positions = Array.isArray(response?.data?.data) ? response.data.data : []

    clearBusMarkers()

    for (let i = 0; i < positions.length; i++) {
      const position = positions[i]
      const lat = parseFloat(String(position.latitude))
      const lng = parseFloat(String(position.longitude))
      if (!Number.isFinite(lat) || !Number.isFinite(lng)) continue

      const marker = L.circleMarker([lat, lng], {
        radius: 7,
        color: '#1e88e5',
        fillColor: '#29b6f6',
        fillOpacity: 0.95,
        weight: 2,
      }).addTo(map)

      const chauffeurName = position?.chauffeur?.name || `Chauffeur #${position?.chauffeur_id ?? ''}`
      marker.bindPopup(`<div style="font-weight:700">${chauffeurName}</div><div>Bus en direct</div>`)
      busMarkers.push(marker)
    }
  } catch (error) {
    console.error('Failed to fetch active bus positions:', error)
  }
}

onMounted(async () => {
  if (!mapEl.value) return

  map = L.map(mapEl.value).setView([36.8065, 10.1815], 13)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map)

  // fetch lignes with arrets and next_departure
  try {
    const response = await apiClient.get('/lignes')
    console.log('lignes response:', response.data)
    const lignes = response.data.data
    console.log('lignes fetched:', lignes)
    console.log('first ligne arrets:', lignes[0]?.arrets)

    const bounds: L.LatLngExpression[] = []

    for (let i = 0; i < lignes.length; i++) {
      const ligne = lignes[i]
      const arrets = Array.isArray(ligne?.arrets) ? ligne.arrets : []
      if (ligne?.is_active !== true || arrets.length < 2) continue

      const coords = arrets
        .map((a: any) => [parseFloat(a.latitude), parseFloat(a.longitude)] as [number, number])
        .filter((coord: [number, number]) => coord.every((value) => Number.isFinite(value)))
      if (coords.length < 2) continue

      const lineColor = ligne?.color && String(ligne.color).trim() ? String(ligne.color) : '#00c853'
      const poly = await drawRoutedLine(map!, arrets, lineColor, bounds)
      if (!poly) continue

      const first = arrets[0]
      const last = arrets[arrets.length - 1]
      const next = ligne.next_departure ? `Prochain départ: ${ligne.next_departure}` : 'Aucun départ aujourd\'hui'

      const content = `<div style="font-weight:700">${ligne.name}</div><div>${first.name} → ${last.name}</div><div style="margin-top:6px">${next}</div>`

      poly.bindTooltip(content, { sticky: true })
      poly.on('mouseover', () => { poly.openTooltip(); })
      poly.on('mouseout', () => { poly.closeTooltip(); })
      poly.on('click', () => { poly.bindPopup(content).openPopup(); })

      // draw arret markers
      for (let j = 0; j < arrets.length; j++) {
        const a = arrets[j]
        const isFirst = j === 0
        const isLast = j === arrets.length - 1
        const marker = L.circleMarker([parseFloat(a.latitude), parseFloat(a.longitude)], {
          radius: isFirst || isLast ? 6 : 4,
          color: isFirst ? '#2ecc71' : isLast ? '#e74c3c' : '#9e9e9e',
          fillColor: isFirst ? '#2ecc71' : isLast ? '#e74c3c' : '#9e9e9e',
          fillOpacity: 0.95,
          weight: 1,
        }).addTo(map!)
        drawnLayers.push(marker)

        const popupText = isFirst ? `Départ: ${a.name}` : isLast ? `Arrivée: ${a.name}` : a.name
        marker.bindPopup(popupText)
      }
    }

    if (bounds.length && map) {
      const group = L.featureGroup(drawnLayers)
      map.fitBounds(group.getBounds(), { padding: [40, 40] })
    }

    await drawActiveBusMarkers()
    busRefreshTimer = window.setInterval(() => {
      void drawActiveBusMarkers()
    }, 5000)
  } catch (e) {
    // ignore fetch errors silently
    console.error(e)
  }
})

onUnmounted(() => {
  if (busRefreshTimer !== null) {
    window.clearInterval(busRefreshTimer)
    busRefreshTimer = null
  }
  clearBusMarkers()
  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <section class="map-page">
    <div class="toast-stack">
      <div v-for="toast in toasts" :key="toast.id" class="toast">{{ toast.message }}</div>
    </div>

    <div class="overlay-panel head">
      <h1>Bus en direct</h1>
      <p>Suivi en temps reel autour des campus de Tunis.</p>
    </div>

    <div ref="mapEl" class="map" />
  </section>
</template>

<style scoped>
.map-page {
  --top-offset: 88px;
  min-height: 100vh;
  position: relative;
  padding: 0;
  padding-top: var(--top-offset);
  overflow: hidden;
  color: #fdf9f0;
}

.head h1 {
  margin: 0;
  font-size: 28px;
  color: #ffe180;
}

.head p {
  margin-top: 8px;
  color: rgba(255,255,255,0.9);
  font-size: 14px;
}

.map {
  width: calc(100% - 48px);
  height: calc(100vh - var(--top-offset) - 48px);
  margin: 12px 24px 24px 24px;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 8px 30px rgba(0,0,0,0.35);
}

.toast-stack {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  top: calc(var(--top-offset) + 8px);
  z-index: 9999;
  display: grid;
  gap: 0.5rem;
  width: min(92vw, 520px);
}

.toast {
  background: rgba(0,0,0,0.6);
  color: #ffffff;
  border-radius: 12px;
  padding: 0.7rem 0.95rem;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.32);
  animation: slideIn 280ms ease;
  border: 1px solid rgba(255,255,255,0.06);
}

.overlay-panel {
  position: absolute;
  bottom: 45px;
  left: 48px;
  z-index: 900;
  background: rgba(0,0,0,0.55);
  color: #ffffff;
  border-radius: 16px;
  padding: 16px 20px;
  max-width: 360px;
  border: 1px solid rgba(255,255,255,0.06);
}

.overlay-panel h1 { color: #ffe180; }
.overlay-panel p { color: rgba(255,255,255,0.9); margin: 6px 0 0; }

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (min-width: 900px) {
  .map {
    height: calc(100vh - var(--top-offset) - 48px);
  }
}
</style>

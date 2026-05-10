<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useBusStore } from '@/stores/busStore'
import { useAuthStore } from '@/stores/authStore'

const mapEl = ref<HTMLDivElement | null>(null)
const busStore = useBusStore()
const authStore = useAuthStore()

const toasts = ref<Array<{ id: number; message: string }>>([])
let toastSeq = 0
let map: L.Map | null = null
const markers = new Map<number, L.CircleMarker>()

const showToast = (message: string) => {
  const id = ++toastSeq
  toasts.value.unshift({ id, message })
  window.setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }, 5000)
}

const upsertMarker = (chauffeurId: number, lat: number, lng: number) => {
  if (!map) {
    return
  }
  const existing = markers.get(chauffeurId)
  if (existing) {
    existing.setLatLng([lat, lng])
    return
  }

  const marker = L.circleMarker([lat, lng], {
    radius: 9,
    color: '#0f84d8',
    fillColor: '#39b2ff',
    fillOpacity: 0.95,
    weight: 2,
  })
    .addTo(map)
    .bindTooltip(`Bus #${chauffeurId}`, { direction: 'top' })

  markers.set(chauffeurId, marker)
}

onMounted(async () => {
  if (!mapEl.value) {
    return
  }

  map = L.map(mapEl.value).setView([36.8065, 10.1815], 13)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map)

  await busStore.fetchActivePositions()
  for (const pos of busStore.positions) {
    upsertMarker(pos.chauffeur_id, pos.latitude, pos.longitude)
    busStore.subscribeToDriver(pos.chauffeur_id)
  }

  if (authStore.membre?.id) {
    busStore.subscribeToAlerts(authStore.membre.id, (data: any) => {
      if (data?.message) {
        showToast(data.message)
      } else {
        showToast('Bus proche de votre arret.')
      }
    })
  }
})

const stopWatch = busStore.$subscribe(() => {
  for (const pos of busStore.positions) {
    upsertMarker(pos.chauffeur_id, pos.latitude, pos.longitude)
  }
})

onUnmounted(() => {
  stopWatch()
  markers.clear()
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
  min-height: 100vh;
  position: relative;
  padding: 0;
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
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
}

.map {
  width: 100%;
  height: calc(100vh - 80px);
  border-radius: 0;
}

.toast-stack {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  top: 0.5rem;
  z-index: 9999;
  display: grid;
  gap: 0.5rem;
  width: min(92vw, 520px);
}

.toast {
  background: rgba(253, 249, 240, 0.08);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  color: #fdf9f0;
  border-radius: 16px;
  padding: 0.7rem 0.95rem;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
  animation: slideIn 280ms ease;
}

.overlay-panel {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 900;
  background: rgba(253, 249, 240, 0.08);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 20px;
  padding: 20px 24px;
  max-width: 360px;
}

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
    height: calc(100vh - 80px);
  }
}
</style>

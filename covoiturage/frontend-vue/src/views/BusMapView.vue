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

    <header class="head">
      <h1>Bus en direct</h1>
      <p>Suivi en temps reel autour des campus de Tunis.</p>
    </header>

    <div ref="mapEl" class="map" />
  </section>
</template>

<style scoped>
.map-page {
  min-height: 100vh;
  background: linear-gradient(160deg, #f7fff2, #e3f2ff 55%, #fffdf7);
  padding: 1rem;
}

.head h1 {
  margin: 0;
  font-size: clamp(1.5rem, 5vw, 2.2rem);
}

.head p {
  margin-top: 0.5rem;
  color: #365577;
}

.map {
  height: calc(100vh - 9rem);
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #bfd8ea;
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
  background: #0a6339;
  color: #fff;
  border-radius: 12px;
  padding: 0.7rem 0.95rem;
  box-shadow: 0 12px 30px rgba(10, 99, 57, 0.2);
  animation: slideIn 280ms ease;
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
  .map-page {
    padding: 1.5rem 2rem;
  }

  .map {
    height: calc(100vh - 10rem);
  }
}
</style>

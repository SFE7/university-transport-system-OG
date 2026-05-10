<script setup lang="ts">
import { computed, onUnmounted, ref } from 'vue'
import { useBusStore } from '@/stores/busStore'
import { useAuthStore } from '@/stores/authStore'

const busStore = useBusStore()
const authStore = useAuthStore()

const isSharing = ref(false)
const watchId = ref<number | null>(null)
const lastPosition = ref<{ latitude: number; longitude: number } | null>(null)
const shareIntervalId = ref<number | null>(null)
const geolocationError = ref<string | null>(null)

const shareLabel = computed(() => (isSharing.value ? 'Partage en cours' : 'Partager ma position'))

const sendPosition = async () => {
  if (!lastPosition.value) {
    return
  }

  await busStore.updateMyPosition({
    latitude: lastPosition.value.latitude,
    longitude: lastPosition.value.longitude,
    is_sharing: true,
  })
}

const startSharing = () => {
  geolocationError.value = null
  if (!navigator.geolocation) {
    geolocationError.value = 'La geolocalisation est indisponible sur cet appareil.'
    return
  }

  if (isSharing.value) {
    return
  }

  watchId.value = navigator.geolocation.watchPosition(
    (pos) => {
      lastPosition.value = {
        latitude: pos.coords.latitude,
        longitude: pos.coords.longitude,
      }
    },
    () => {
      geolocationError.value = 'Impossible de recuperer votre position.'
    },
    { maximumAge: 0, enableHighAccuracy: true }
  )

  shareIntervalId.value = window.setInterval(() => {
    void sendPosition()
  }, 3000)
  isSharing.value = true
}

const stopSharing = async () => {
  if (watchId.value !== null) {
    navigator.geolocation.clearWatch(watchId.value)
    watchId.value = null
  }
  if (shareIntervalId.value !== null) {
    window.clearInterval(shareIntervalId.value)
    shareIntervalId.value = null
  }

  await busStore.stopSharing()
  isSharing.value = false
}

onUnmounted(() => {
  if (watchId.value !== null) {
    navigator.geolocation.clearWatch(watchId.value)
  }
  if (shareIntervalId.value !== null) {
    window.clearInterval(shareIntervalId.value)
  }
})
</script>

<template>
  <section class="driver-page">
    <div class="hero">
      <h1 class="page-title">Espace Chauffeur Bus</h1>
      <p>
        {{ authStore.membre?.name }} peut partager sa position en direct avec les etudiants.
      </p>
    </div>

    <div class="sharing-card" :class="{ active: isSharing }">
      <div class="indicator-wrap">
        <span class="indicator" />
        <span class="share-label">Position partagée en direct</span>
      </div>
      <p class="share-state">{{ shareLabel }}</p>
      <p v-if="busStore.error" class="error">{{ busStore.error }}</p>
      <p v-if="geolocationError" class="error">{{ geolocationError }}</p>
      <p v-if="lastPosition" class="coords">
        Lat: {{ lastPosition.latitude }}, Lng: {{ lastPosition.longitude }}
      </p>
    </div>

    <div class="actions">
      <button class="btn primary" :disabled="isSharing" @click="startSharing">Partager ma position</button>
      <button class="btn danger" :disabled="!isSharing" @click="stopSharing">Arreter</button>
    </div>
  </section>
</template>

<style scoped>
.driver-page {
  min-height: 100vh;
  padding: 40px 24px;
  max-width: 700px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.hero h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  text-align: center;
  color: #ffe180;
}

.hero p {
  margin-top: 8px;
  margin-bottom: 40px;
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  text-align: center;
}

.sharing-card {
  background: rgba(253, 249, 240, 0.08);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 24px;
  padding: 48px;
  width: 100%;
  text-align: center;
}

.indicator-wrap {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  justify-content: center;
}

.indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #6ec47a;
}

.active .indicator {
  box-shadow: 0 0 0 0 rgba(110, 196, 122, 0.45);
  animation: pulse 1.5s infinite;
}

.share-label {
  color: #6ec47a;
  font-size: 14px;
  font-weight: 600;
}

.share-state {
  margin: 14px 0 0;
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
}

.coords {
  margin-top: 20px;
  color: rgba(253, 249, 240, 0.5);
  font-size: 12px;
  font-family: monospace;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  70% {
    transform: scale(1.4);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.actions {
  margin-top: 24px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  width: 100%;
  justify-items: center;
}

.btn {
  border-radius: 999px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.primary {
  background: #ffe180;
  color: #1b3d2f;
  font-size: 18px;
  padding: 18px 48px;
  width: 100%;
  max-width: 320px;
}

.danger {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
  font-size: 15px;
  padding: 14px 40px;
  width: 100%;
  max-width: 320px;
  margin-top: 16px;
}

.primary:hover:not(:disabled) {
  background: #9f9065;
  color: #fdf9f0;
}

.danger:hover:not(:disabled) {
  background: rgba(255, 107, 107, 0.3);
}

.error {
  margin: 12px 0 0;
  color: #ff6b6b;
}

@media (min-width: 760px) {
  .driver-page {
    padding: 40px 24px;
  }

  .actions {
    grid-template-columns: 1fr;
  }
}
</style>

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
      <h1>Espace Chauffeur Bus</h1>
      <p>
        {{ authStore.membre?.name }} peut partager sa position en direct avec les etudiants.
      </p>
    </div>

    <div class="status-card" :class="{ active: isSharing }">
      <div class="indicator-wrap">
        <span class="indicator" />
        <span>{{ shareLabel }}</span>
      </div>
      <p v-if="busStore.error" class="error">{{ busStore.error }}</p>
      <p v-if="geolocationError" class="error">{{ geolocationError }}</p>
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
  padding: 2rem 1rem;
  background: radial-gradient(circle at top left, #d4ffe9, #f6fbff 45%, #ffffff);
  color: #123;
}

.hero h1 {
  margin: 0;
  font-size: clamp(1.8rem, 6vw, 2.6rem);
}

.hero p {
  margin-top: 0.75rem;
  color: #3b4a66;
}

.status-card {
  margin-top: 1.5rem;
  border-radius: 18px;
  border: 1px solid #d6dfeb;
  padding: 1rem;
  background: #fff;
}

.indicator-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 700;
}

.indicator {
  width: 14px;
  height: 14px;
  border-radius: 999px;
  background: #8d98ab;
}

.active .indicator {
  background: #17b56a;
  box-shadow: 0 0 0 0 rgba(23, 181, 106, 0.55);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(23, 181, 106, 0.55);
  }
  70% {
    box-shadow: 0 0 0 14px rgba(23, 181, 106, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(23, 181, 106, 0);
  }
}

.actions {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

.btn {
  border: 0;
  border-radius: 12px;
  padding: 0.9rem 1rem;
  font-weight: 700;
  cursor: pointer;
}

.btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.primary {
  background: #0e7a3f;
  color: #fff;
}

.danger {
  background: #d13838;
  color: #fff;
}

.error {
  margin: 0.6rem 0 0;
  color: #b1122a;
}

@media (min-width: 760px) {
  .driver-page {
    padding: 3rem;
  }

  .actions {
    grid-template-columns: repeat(2, minmax(0, 220px));
  }
}
</style>

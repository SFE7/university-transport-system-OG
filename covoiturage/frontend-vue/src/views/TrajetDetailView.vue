<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'

const route = useRoute()
const authStore = useAuthStore()
const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

const actionMessage = ref('')
const actionError = ref('')

const trajetId = computed(() => Number(route.params.id))

const canReserve = computed(() => {
  const trajet = trajetStore.currentTrajet
  return (
    authStore.isAuthenticated &&
    !authStore.isConducteur &&
    trajet?.status === 'active'
  )
})

const handleReserve = async () => {
  actionMessage.value = ''
  actionError.value = ''
  try {
    await reservationStore.create({ trajet_id: trajetId.value })
    actionMessage.value = 'Reservation envoyee.'
  } catch {
    actionError.value = reservationStore.error || 'Reservation failed'
  }
}

onMounted(() => {
  trajetStore.fetchOne(trajetId.value)
})
</script>

<template>
  <section class="page">
    <div v-if="trajetStore.currentTrajet" class="card">
      <div class="trajet-header">
        <div>
          <h1 class="page-title">
            {{ trajetStore.currentTrajet.departure_point }} ->
            {{ trajetStore.currentTrajet.arrival_point }}
          </h1>
          <p class="subtitle">Depart: {{ trajetStore.currentTrajet.departure_time }}</p>
        </div>
        <div class="tag-list">
          <span class="badge">{{ trajetStore.currentTrajet.status }}</span>
          <span class="badge gray">{{ trajetStore.currentTrajet.available_seats }} places</span>
        </div>
      </div>
      <div class="divider"></div>
      <div class="grid two">
        <div>
          <h3>Conducteur</h3>
          <p class="muted">
            {{ trajetStore.currentTrajet.conducteur?.name || 'Conducteur inconnu' }}
          </p>
        </div>
        <div>
          <h3>Membre ID</h3>
          <p class="muted">{{ trajetStore.currentTrajet.membre_id }}</p>
        </div>
      </div>
      <div class="form-actions">
        <button v-if="canReserve" class="btn" type="button" @click="handleReserve">
          Reserver
        </button>
        <span v-else class="status">Connexion requise ou trajet indisponible.</span>
      </div>
      <p v-if="actionMessage" class="status">{{ actionMessage }}</p>
      <p v-if="actionError" class="status">{{ actionError }}</p>
    </div>
    <div v-else class="status">Chargement du trajet...</div>
  </section>
</template>

<style scoped>
.trajet-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}
</style>

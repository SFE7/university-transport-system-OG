<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'
import { getCarPhotoUrl } from '@/utils/carPhoto'

const route = useRoute()
const authStore = useAuthStore()
const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

const actionMessage = ref('')
const actionError = ref('')

const trajetId = computed(() => Number(route.params.id))

const photoUrl = computed(() => {
  const trajet = trajetStore.currentTrajet
  if (!trajet) {
    return null
  }

  return (
    trajet.car_photo_url ??
    getCarPhotoUrl(trajet.car_category ?? '', trajet.car_model ?? '') ??
    null
  )
})

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
  <section class="page page-shell">
    <div v-if="trajetStore.currentTrajet" class="glass detail-card">
      <div class="trajet-header">
        <div>
          <h1 class="page-title">
            {{ trajetStore.currentTrajet.departure_point }} ->
            {{ trajetStore.currentTrajet.arrival_point }}
          </h1>
          <p class="subtitle">Depart: {{ trajetStore.currentTrajet.departure_time }}</p>
        </div>
        <div class="tag-list">
          <span
            class="status-pill"
            :class="{
              active:
                String(trajetStore.currentTrajet.status) === 'active' ||
                String(trajetStore.currentTrajet.status) === 'accepted',
              pending:
                String(trajetStore.currentTrajet.status) === 'full' ||
                String(trajetStore.currentTrajet.status) === 'pending',
              cancelled:
                String(trajetStore.currentTrajet.status) === 'cancelled' ||
                String(trajetStore.currentTrajet.status) === 'refused',
            }"
          >
            {{ trajetStore.currentTrajet.status }}
          </span>
          <span class="status-pill pending">{{ trajetStore.currentTrajet.available_seats }} places</span>
        </div>
      </div>
      <div v-if="photoUrl" class="detail-photo">
        <img :src="photoUrl" :alt="trajetStore.currentTrajet.car_model || 'Photo vehicule'" />
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
        <div>
          <h3>Vehicule</h3>
          <p class="muted">
            {{ trajetStore.currentTrajet.car_category || 'Categorie inconnue' }}
            <span v-if="trajetStore.currentTrajet.car_model">
              - {{ trajetStore.currentTrajet.car_model }}
            </span>
          </p>
        </div>
        <div>
          <h3>Photo du vehicule</h3>
          <p class="muted">{{ trajetStore.currentTrajet.car_photo_url ? 'Disponible' : 'Aucune photo' }}</p>
        </div>
      </div>
      <div class="form-actions">
        <button v-if="canReserve" class="primary-btn" type="button" @click="handleReserve">
          Reserver
        </button>
        <span v-else class="status">Connexion requise ou trajet indisponible.</span>
      </div>
      <p v-if="actionMessage" class="success-message">{{ actionMessage }}</p>
      <p v-if="actionError" class="error-message">{{ actionError }}</p>
    </div>
    <div v-else class="empty-state">Chargement du trajet...</div>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 900px;
  margin: 0 auto;
}

.trajet-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.detail-card {
  padding: 20px 24px;
  border-radius: 16px;
}

.detail-photo {
  margin: 18px 0;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(253, 249, 240, 0.12);
  background: rgba(253, 249, 240, 0.05);
}

.detail-photo img {
  display: block;
  width: 100%;
  max-height: 260px;
  object-fit: cover;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid transparent;
  margin-right: 8px;
}

.status-pill.active {
  background: rgba(100, 200, 120, 0.15);
  color: #6ec47a;
  border-color: rgba(100, 200, 120, 0.3);
}

.status-pill.pending {
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border-color: rgba(255, 225, 128, 0.3);
}

.status-pill.cancelled {
  background: rgba(255, 107, 107, 0.15);
  color: #ff6b6b;
  border-color: rgba(255, 107, 107, 0.3);
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.success-message {
  color: #6ec47a;
  font-size: 13px;
  margin: 0;
}

.error-message {
  color: #ff6b6b;
  font-size: 13px;
  margin: 0;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}

@media (max-width: 640px) {
  .trajet-header {
    flex-direction: column;
  }
}
</style>

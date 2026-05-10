<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'

const authStore = useAuthStore()
const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

onMounted(() => {
  trajetStore.fetchHistory()
  reservationStore.fetchMyReservations()
})

const myTrajets = computed(() => {
  if (Array.isArray(trajetStore.history)) {
    return trajetStore.history
  }

  const historyPayload = trajetStore.history as unknown as { data?: unknown }
  return Array.isArray(historyPayload?.data) ? historyPayload.data : []
})

const showForm = ref(false)
const formError = ref('')
const newTrajet = ref({
  departure_point: '',
  arrival_point: '',
  departure_time: '',
  available_seats: 1,
})

const submitTrajet = async () => {
  formError.value = ''
  if (
    !newTrajet.value.departure_point ||
    !newTrajet.value.arrival_point ||
    !newTrajet.value.departure_time
  ) {
    formError.value = 'Tous les champs sont obligatoires.'
    return
  }

  try {
    await trajetStore.create(newTrajet.value)
    showForm.value = false
    newTrajet.value = {
      departure_point: '',
      arrival_point: '',
      departure_time: '',
      available_seats: 1,
    }
    await trajetStore.fetchHistory()
  } catch (e) {
    formError.value = 'Erreur lors de la création du trajet.'
  }
}

const pendingReservations = computed(() =>
  Array.isArray(reservationStore.reservations)
    ? reservationStore.reservations.filter(
        (reservation) =>
          reservation.status === 'pending' && reservation.trajet?.status === 'active'
      )
    : []
)
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Dashboard conducteur</h1>
      <p class="subtitle">Pilotez vos trajets et demandes.</p>
    </header>

    <div class="grid two">
      <section class="glass dashboard-card">
        <h2 class="section-title">Mes trajets</h2>
        <div v-if="showForm" class="glass form-panel">
          <h3 class="subsection-title">Nouveau trajet</h3>
          <div class="form-grid">
            <input v-model="newTrajet.departure_point" class="field-input" placeholder="Point de départ" />
            <input v-model="newTrajet.arrival_point" class="field-input" placeholder="Point d'arrivée" />
            <input v-model="newTrajet.departure_time" class="field-input" type="datetime-local" />
            <input v-model.number="newTrajet.available_seats" class="field-input" type="number" min="1" placeholder="Places disponibles" />
            <div class="button-row">
              <button class="primary-btn" type="button" @click="submitTrajet">Créer</button>
              <button class="ghost-btn" type="button" @click="showForm = false">Annuler</button>
            </div>
            <p v-if="formError" class="error-message">{{ formError }}</p>
          </div>
        </div>

        <button v-else class="primary-btn new-trajet-btn" type="button" @click="showForm = true">
          + Nouveau trajet
        </button>
        <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
        <div v-if="myTrajets.length" class="items-list">
          <article v-for="trajet in myTrajets" :key="trajet.id" class="glass item-card">
          <div class="tag-list">
            <span
              class="status-pill"
              :class="{
                active: String(trajet.status) === 'active',
                pending: String(trajet.status) === 'full',
                cancelled: String(trajet.status) === 'cancelled',
              }"
            >
              {{ trajet.status }}
            </span>
            <span class="status-pill pending">{{ trajet.departure_time }}</span>
          </div>
          <p class="route-text">{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
          <div class="button-row">
            <button
              v-if="String(trajet.status) === 'active'"
              class="danger-btn"
              type="button"
              @click="trajetStore.cancel(trajet.id)"
            >
              Annuler
            </button>
          </div>
          </article>
        </div>
        <p v-if="!myTrajets.length" class="empty-state">Aucun trajet pour l'instant.</p>
      </section>

      <section class="glass dashboard-card">
        <h2 class="section-title">Reservations en attente</h2>
        <div v-if="reservationStore.isLoading" class="status">Chargement...</div>
        <div v-if="pendingReservations.length" class="items-list">
          <article v-for="reservation in pendingReservations" :key="reservation.id" class="glass item-card">
            <p class="muted">Passager: {{ reservation.membre_id }}</p>
            <p class="muted">Demande: {{ reservation.created_at }}</p>
            <div class="button-row">
              <button class="accept-btn" type="button" @click="reservationStore.accept(reservation.id)">
              Accepter
              </button>
              <button class="danger-btn" type="button" @click="reservationStore.refuse(reservation.id)">
              Refuser
              </button>
            </div>
          </article>
        </div>
        <p v-if="!pendingReservations.length" class="empty-state">Aucune demande en attente.</p>
      </section>
    </div>

    <hr class="divider" />

    <p v-if="!authStore.isConducteur" class="status">Acces reserve aux conducteurs.</p>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.dashboard-card {
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 16px;
}

.dashboard-card:hover,
.item-card:hover,
.form-panel:hover {
  border-color: rgba(255, 225, 128, 0.5);
}

.section-title {
  color: #9f9065;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 16px;
}

.subsection-title {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 600;
  color: #fdf9f0;
}

.form-panel {
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 20px;
}

.form-grid {
  display: grid;
  gap: 12px;
}

.field-input {
  width: 100%;
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 10px 14px;
  font-family: inherit;
  font-size: 14px;
}

.field-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.field-input:focus {
  outline: none;
  border-color: #ffe180;
}

.button-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.primary-btn,
.accept-btn,
.danger-btn,
.ghost-btn {
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.primary-btn,
.new-trajet-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
}

.primary-btn:hover,
.new-trajet-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.accept-btn {
  background: rgba(100, 200, 120, 0.15);
  border: 1px solid rgba(100, 200, 120, 0.3);
  color: #6ec47a;
  padding: 8px 20px;
}

.accept-btn:hover {
  background: rgba(100, 200, 120, 0.3);
}

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
  padding: 8px 20px;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.ghost-btn {
  background: transparent;
  border: 1px solid rgba(253, 249, 240, 0.15);
  color: #fdf9f0;
  padding: 10px 24px;
}

.ghost-btn:hover {
  border-color: rgba(255, 225, 128, 0.35);
}

.items-list {
  display: grid;
  gap: 16px;
}

.item-card {
  padding: 20px 24px;
  border-radius: 16px;
}

.route-text {
  margin: 12px 0 0;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
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

.divider {
  border: none;
  border-top: 1px solid rgba(253, 249, 240, 0.08);
  margin: 24px 0;
}

@media (max-width: 860px) {
  .grid.two {
    grid-template-columns: 1fr;
  }
}
</style>

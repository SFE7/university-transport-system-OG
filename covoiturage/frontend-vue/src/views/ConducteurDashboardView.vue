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
  <section class="page">
    <header>
      <h1 class="page-title">Dashboard conducteur</h1>
      <p class="subtitle">Pilotez vos trajets et demandes.</p>
    </header>

    <div class="grid two">
      <section class="card">
        <h2>Mes trajets</h2>
        <div v-if="showForm" style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--color-background-secondary); border-radius: var(--border-radius-lg); border: 1px solid var(--color-border-tertiary)">
          <h3 style="margin: 0 0 1rem; font-size: 16px; font-weight: 500">Nouveau trajet</h3>
          <div style="display: grid; gap: 0.75rem">
            <input v-model="newTrajet.departure_point" placeholder="Point de départ" style="padding: 0.6rem 0.75rem; border-radius: var(--border-radius-md); border: 1px solid var(--color-border-secondary); background: var(--color-background-primary); color: var(--color-text-primary); font-size: 14px" />
            <input v-model="newTrajet.arrival_point" placeholder="Point d'arrivée" style="padding: 0.6rem 0.75rem; border-radius: var(--border-radius-md); border: 1px solid var(--color-border-secondary); background: var(--color-background-primary); color: var(--color-text-primary); font-size: 14px" />
            <input v-model="newTrajet.departure_time" type="datetime-local" style="padding: 0.6rem 0.75rem; border-radius: var(--border-radius-md); border: 1px solid var(--color-border-secondary); background: var(--color-background-primary); color: var(--color-text-primary); font-size: 14px" />
            <input v-model.number="newTrajet.available_seats" type="number" min="1" placeholder="Places disponibles" style="padding: 0.6rem 0.75rem; border-radius: var(--border-radius-md); border: 1px solid var(--color-border-secondary); background: var(--color-background-primary); color: var(--color-text-primary); font-size: 14px" />
            <div style="display: flex; gap: 0.5rem">
              <button @click="submitTrajet" style="padding: 0.6rem 1.25rem; background: #e85d24; color: white; border: none; border-radius: var(--border-radius-md); cursor: pointer; font-size: 14px">Créer</button>
              <button @click="showForm = false" style="padding: 0.6rem 1.25rem; background: transparent; color: var(--color-text-secondary); border: 1px solid var(--color-border-secondary); border-radius: var(--border-radius-md); cursor: pointer; font-size: 14px">Annuler</button>
            </div>
            <p v-if="formError" style="color: var(--color-text-danger); font-size: 13px; margin: 0">{{ formError }}</p>
          </div>
        </div>

        <button v-else @click="showForm = true" style="margin-bottom: 1rem; padding: 0.6rem 1.25rem; background: #e85d24; color: white; border: none; border-radius: var(--border-radius-md); cursor: pointer; font-size: 14px">
          + Nouveau trajet
        </button>
        <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
        <div v-for="trajet in myTrajets" :key="trajet.id" class="card soft">
          <div class="tag-list">
            <span class="badge">{{ trajet.status }}</span>
            <span class="badge gray">{{ trajet.departure_time }}</span>
          </div>
          <p>{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
          <button
            v-if="trajet.status === 'active'"
            class="btn ghost"
            type="button"
            @click="trajetStore.cancel(trajet.id)"
          >
            Annuler
          </button>
        </div>
        <p v-if="!myTrajets.length" class="muted">Aucun trajet pour l'instant.</p>
      </section>

      <section class="card">
        <h2>Reservations en attente</h2>
        <div v-if="reservationStore.isLoading" class="status">Chargement...</div>
        <div v-for="reservation in pendingReservations" :key="reservation.id" class="card soft">
          <p class="muted">Passager: {{ reservation.membre_id }}</p>
          <p class="muted">Demande: {{ reservation.created_at }}</p>
          <div class="form-actions">
            <button class="btn" type="button" @click="reservationStore.accept(reservation.id)">
              Accepter
            </button>
            <button class="btn ghost" type="button" @click="reservationStore.refuse(reservation.id)">
              Refuser
            </button>
          </div>
        </div>
        <p v-if="!pendingReservations.length" class="muted">Aucune demande en attente.</p>
      </section>
    </div>

    <p v-if="!authStore.isConducteur" class="status">Acces reserve aux conducteurs.</p>
  </section>
</template>

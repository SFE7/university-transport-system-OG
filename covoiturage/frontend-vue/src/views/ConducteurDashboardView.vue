<script setup lang="ts">
import { computed, onMounted } from 'vue'
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

const myTrajets = computed(() => trajetStore.history)

const pendingReservations = computed(() =>
  reservationStore.reservations.filter(
    (reservation) =>
      reservation.status === 'pending' && reservation.trajet?.status === 'active'
  )
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

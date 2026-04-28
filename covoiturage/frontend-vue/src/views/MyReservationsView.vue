<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useReservationStore } from '@/stores/reservationStore'

const reservationStore = useReservationStore()

const canCancel = (status) => status === 'pending' || status === 'accepted'

onMounted(() => {
  reservationStore.fetchMyReservations()
})

const reservations = computed(() => reservationStore.reservations)
</script>

<template>
  <section class="page">
    <header>
      <h1 class="page-title">Mes reservations</h1>
      <p class="subtitle">Suivez l'etat de vos demandes.</p>
    </header>

    <div v-if="reservationStore.isLoading" class="status">Chargement...</div>
    <div v-if="reservationStore.error" class="status">{{ reservationStore.error }}</div>

    <div class="grid two">
      <div v-for="reservation in reservations" :key="reservation.id" class="card">
        <div class="tag-list">
          <span class="badge">{{ reservation.status }}</span>
          <span class="badge gray">{{ reservation.created_at }}</span>
        </div>
        <h3>
          {{ reservation.trajet?.departure_point || 'Depart' }} ->
          {{ reservation.trajet?.arrival_point || 'Arrivee' }}
        </h3>
        <p class="muted">Depart: {{ reservation.trajet?.departure_time || 'N/A' }}</p>
        <button
          v-if="canCancel(reservation.status)"
          class="btn ghost"
          type="button"
          @click="reservationStore.cancel(reservation.id)"
        >
          Annuler
        </button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useReservationStore } from '@/stores/reservationStore'

const reservationStore = useReservationStore()

const canCancel = (status: string) => status === 'pending' || status === 'accepted'

onMounted(() => {
  reservationStore.fetchMyReservations()
})

const reservations = computed(() => reservationStore.reservations)
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Mes reservations</h1>
      <p class="subtitle">Suivez l'etat de vos demandes.</p>
    </header>

    <div v-if="reservationStore.isLoading" class="status">Chargement...</div>
    <div v-if="reservationStore.error" class="status">{{ reservationStore.error }}</div>

    <div v-if="reservations.length" class="grid two">
      <div v-for="reservation in reservations" :key="reservation.id" class="glass reservation-card">
        <div class="tag-list">
          <span
            class="status-pill"
            :class="{
              active: reservation.status === 'accepted',
              pending: reservation.status === 'pending',
              cancelled: reservation.status === 'cancelled' || reservation.status === 'refused',
            }"
          >
            {{ reservation.status }}
          </span>
          <span class="status-pill pending">{{ reservation.created_at }}</span>
        </div>
        <h3>
          {{ reservation.trajet?.departure_point || 'Depart' }} ->
          {{ reservation.trajet?.arrival_point || 'Arrivee' }}
        </h3>
        <p class="muted">Depart: {{ reservation.trajet?.departure_time || 'N/A' }}</p>
        <button
          v-if="canCancel(reservation.status)"
          class="danger-btn"
          type="button"
          @click="reservationStore.cancel(reservation.id)"
        >
          Annuler
        </button>
      </div>
    </div>
    <div v-else class="empty-state">Aucune reservation pour le moment.</div>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.reservation-card {
  padding: 20px 24px;
  border-radius: 16px;
  transition: border-color 0.2s ease;
}

.reservation-card:hover {
  border-color: rgba(255, 225, 128, 0.5);
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

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
  border-radius: 999px;
  padding: 10px 24px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}
</style>

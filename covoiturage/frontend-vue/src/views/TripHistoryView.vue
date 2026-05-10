<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useTrajetStore } from '@/stores/trajetStore'

const trajetStore = useTrajetStore()

onMounted(() => {
  trajetStore.fetchHistory()
})

const history = computed(() => trajetStore.history)
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Historique des trajets</h1>
      <p class="subtitle">Vos trajets termines et annules.</p>
    </header>

    <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
    <div v-if="trajetStore.error" class="status">{{ trajetStore.error }}</div>

    <div v-if="history.length" class="grid two">
      <div v-for="trajet in history" :key="trajet.id" class="glass history-card">
        <div class="tag-list">
          <span
            class="status-pill"
            :class="{
              active: String(trajet.status) === 'completed',
              pending: String(trajet.status) === 'active' || String(trajet.status) === 'pending',
              cancelled: String(trajet.status) === 'cancelled' || String(trajet.status) === 'refused',
            }"
          >
            {{ trajet.status }}
          </span>
          <span class="status-pill pending">{{ trajet.departure_time }}</span>
        </div>
        <p>{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
        <p class="muted">Places: {{ trajet.available_seats }}</p>
      </div>
    </div>
    <div v-else class="empty-state">Aucun trajet dans l'historique.</div>
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

.history-card {
  padding: 20px 24px;
  border-radius: 16px;
  transition: border-color 0.2s ease;
}

.history-card:hover {
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

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}
</style>

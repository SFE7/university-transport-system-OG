<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useTrajetStore } from '@/stores/trajetStore'
import TrajetCard from '@/components/TrajetCard.vue'

const trajetStore = useTrajetStore()

const filters = ref({
  departure_point: '',
  arrival_point: '',
  departure_time: '',
  available_seats: '',
})

const buildFilters = () => ({
  ...(filters.value.departure_point
    ? { departure_point: filters.value.departure_point }
    : {}),
  ...(filters.value.arrival_point ? { arrival_point: filters.value.arrival_point } : {}),
  ...(filters.value.departure_time ? { departure_time: filters.value.departure_time } : {}),
  ...(filters.value.available_seats
    ? { available_seats: Number(filters.value.available_seats) }
    : {}),
})

watch(
  filters,
  () => {
    trajetStore.fetchAll(buildFilters())
  },
  { deep: true }
)

onMounted(() => {
  trajetStore.fetchAll()
})
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Explorer les trajets</h1>
      <p class="subtitle">Filtrez selon vos besoins et reservez rapidement.</p>
    </header>

    <div class="glass filter-panel">
      <div class="grid two">
        <label class="field">
          <span class="field-label">Depart</span>
          <input v-model="filters.departure_point" class="filter-input" type="text" placeholder="Campus" />
        </label>
        <label class="field">
          <span class="field-label">Arrivee</span>
          <input v-model="filters.arrival_point" class="filter-input" type="text" placeholder="Centre ville" />
        </label>
        <label class="field">
          <span class="field-label">Horaire</span>
          <input v-model="filters.departure_time" class="filter-input" type="datetime-local" />
        </label>
        <label class="field">
          <span class="field-label">Places min</span>
          <input v-model="filters.available_seats" class="filter-input" type="number" min="1" />
        </label>
      </div>
    </div>

    <div v-if="trajetStore.isLoading" class="status">Chargement des trajets...</div>
    <div v-if="trajetStore.error" class="status">{{ trajetStore.error }}</div>
    <div v-else-if="!trajetStore.trajets.length" class="empty-state">Aucun trajet ne correspond a vos criteres.</div>

    <div class="trajets-list">
      <TrajetCard v-for="trajet in trajetStore.trajets" :key="trajet.id" :trajet="trajet" />
    </div>
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

.trajets-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.filter-panel {
  padding: 20px 24px;
  border-radius: 16px;
  margin-bottom: 20px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  color: #fdf9f0;
  font-size: 14px;
  font-weight: 500;
}

.filter-input {
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 10px 14px;
  width: 100%;
  font-family: inherit;
}

.filter-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.filter-input:focus {
  outline: none;
  border-color: #ffe180;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}
</style>

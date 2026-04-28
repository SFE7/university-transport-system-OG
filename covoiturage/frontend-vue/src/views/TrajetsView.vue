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
  <section class="page">
    <header>
      <h1 class="page-title">Explorer les trajets</h1>
      <p class="subtitle">Filtrez selon vos besoins et reservez rapidement.</p>
    </header>

    <div class="card soft">
      <div class="grid two">
        <label>
          Depart
          <input v-model="filters.departure_point" class="input" type="text" placeholder="Campus" />
        </label>
        <label>
          Arrivee
          <input v-model="filters.arrival_point" class="input" type="text" placeholder="Centre ville" />
        </label>
        <label>
          Horaire
          <input v-model="filters.departure_time" class="input" type="datetime-local" />
        </label>
        <label>
          Places min
          <input v-model="filters.available_seats" class="input" type="number" min="1" />
        </label>
      </div>
    </div>

    <div v-if="trajetStore.isLoading" class="status">Chargement des trajets...</div>
    <div v-if="trajetStore.error" class="status">{{ trajetStore.error }}</div>

    <div class="grid three">
      <TrajetCard v-for="trajet in trajetStore.trajets" :key="trajet.id" :trajet="trajet" />
    </div>
  </section>
</template>

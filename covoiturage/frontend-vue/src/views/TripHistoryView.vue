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
  <section class="page">
    <header>
      <h1 class="page-title">Historique des trajets</h1>
      <p class="subtitle">Vos trajets termines et annules.</p>
    </header>

    <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
    <div v-if="trajetStore.error" class="status">{{ trajetStore.error }}</div>

    <div class="grid two">
      <div v-for="trajet in history" :key="trajet.id" class="card">
        <div class="tag-list">
          <span class="badge">{{ trajet.status }}</span>
          <span class="badge gray">{{ trajet.departure_time }}</span>
        </div>
        <p>{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
        <p class="muted">Places: {{ trajet.available_seats }}</p>
      </div>
    </div>
  </section>
</template>

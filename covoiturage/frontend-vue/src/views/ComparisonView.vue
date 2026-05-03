<script setup lang="ts">
import { reactive } from 'vue'
import { useComparisonStore } from '@/stores/comparisonStore'

const comparisonStore = useComparisonStore()

const form = reactive({
  departure: '',
  arrival: '',
  datetime: '',
})

const submit = async () => {
  await comparisonStore.compare({
    departure: form.departure,
    arrival: form.arrival,
    datetime: form.datetime,
  })
}
</script>

<template>
  <section class="compare-page">
    <header>
      <h1>Comparateur Bus vs Covoiturage</h1>
      <p>Trouvez l'option la plus rapide et la plus economique.</p>
    </header>

    <form class="search" @submit.prevent="submit">
      <input v-model="form.departure" type="text" placeholder="Depart" required />
      <input v-model="form.arrival" type="text" placeholder="Arrivee" required />
      <input v-model="form.datetime" type="datetime-local" required />
      <button type="submit" :disabled="comparisonStore.isLoading">Comparer</button>
    </form>

    <p v-if="comparisonStore.error" class="error">{{ comparisonStore.error }}</p>

    <div v-if="comparisonStore.result" class="summary">
      <span class="badge">Plus rapide: {{ comparisonStore.result.summary.fastest }}</span>
      <span class="badge">Moins cher: {{ comparisonStore.result.summary.cheapest }}</span>
    </div>

    <div v-if="comparisonStore.result" class="grid">
      <div class="column">
        <h3>Covoiturage</h3>
        <article
          v-for="trajet in comparisonStore.result.covoiturage"
          :key="trajet.id"
          class="card"
        >
          <p><strong>{{ trajet.departure_point }}</strong> → <strong>{{ trajet.arrival_point }}</strong></p>
          <p>{{ trajet.departure_time }}</p>
          <p>Places: {{ trajet.available_seats }}</p>
        </article>
      </div>

      <div class="column">
        <h3>Bus</h3>
        <article
          v-for="horaire in comparisonStore.result.bus"
          :key="horaire.id"
          class="card"
        >
          <p>Ligne #{{ horaire.ligne_bus_id }}</p>
          <p>Depart: {{ horaire.departure_time }}</p>
          <p>Jours: {{ horaire.days.join(', ') }}</p>
        </article>
      </div>
    </div>
  </section>
</template>

<style scoped>
.compare-page {
  min-height: 100vh;
  padding: 1rem;
  background: radial-gradient(circle at 20% 20%, #eaf4ff, #fff7e6 40%, #fff);
}

.search {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.55rem;
}

.search input,
.search button {
  border-radius: 10px;
  border: 1px solid #d7dde8;
  padding: 0.65rem;
}

.search button {
  background: #164f88;
  color: #fff;
  border: 0;
  cursor: pointer;
}

.summary {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.badge {
  border-radius: 999px;
  background: #ffd16f;
  padding: 0.35rem 0.8rem;
  font-weight: 700;
}

.grid {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.9rem;
}

.column h3 {
  margin: 0 0 0.5rem;
}

.card {
  border: 1px solid #e2d8c9;
  border-radius: 12px;
  padding: 0.75rem;
  background: #fff;
}

.error {
  color: #c31a3a;
}

@media (min-width: 980px) {
  .compare-page {
    padding: 2rem;
  }

  .search {
    grid-template-columns: 1fr 1fr 1fr auto;
  }

  .grid {
    grid-template-columns: 1fr 1fr;
  }
}
</style>

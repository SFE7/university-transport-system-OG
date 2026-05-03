<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useLigneStore } from '@/stores/ligneStore'

const ligneStore = useLigneStore()

const dayButtons = [
  { label: 'Lun', value: 'monday' },
  { label: 'Mar', value: 'tuesday' },
  { label: 'Mer', value: 'wednesday' },
  { label: 'Jeu', value: 'thursday' },
  { label: 'Ven', value: 'friday' },
  { label: 'Sam', value: 'saturday' },
  { label: 'Dim', value: 'sunday' },
]

const selectedDay = ref<string>('monday')
const schedulesByLigne = ref<Record<number, string[]>>({})

const loadSchedulesForAll = async () => {
  const map: Record<number, string[]> = {}
  for (const ligne of ligneStore.lignes) {
    const response = await ligneStore.fetchSchedules(ligne.id, selectedDay.value)
    map[ligne.id] = response.data.map((h) => h.departure_time)
  }
  schedulesByLigne.value = map
}

const selectDay = async (day: string) => {
  selectedDay.value = day
  await loadSchedulesForAll()
}

const sortedStops = (ligneId: number) => {
  const ligne = ligneStore.lignes.find((item) => item.id === ligneId)
  if (!ligne?.arrets) {
    return []
  }
  return [...ligne.arrets].sort((a, b) => a.order - b.order)
}

const loaded = computed(() => !ligneStore.isLoading && ligneStore.lignes.length > 0)

onMounted(async () => {
  await ligneStore.fetchAll()
  await loadSchedulesForAll()
})
</script>

<template>
  <section class="schedule-page">
    <header>
      <h1>Horaires des Bus</h1>
      <p>Choisissez un jour pour voir les passages prevus.</p>
    </header>

    <div class="day-filter">
      <button
        v-for="d in dayButtons"
        :key="d.value"
        class="day-btn"
        :class="{ active: selectedDay === d.value }"
        @click="selectDay(d.value)"
      >
        {{ d.label }}
      </button>
    </div>

    <p v-if="ligneStore.error" class="error">{{ ligneStore.error }}</p>
    <p v-if="ligneStore.isLoading" class="muted">Chargement...</p>

    <div v-if="loaded" class="ligne-list">
      <article v-for="ligne in ligneStore.lignes" :key="ligne.id" class="ligne-card">
        <h3>{{ ligne.name }}</h3>
        <p>{{ ligne.description || 'Sans description' }}</p>

        <div class="stops">
          <strong>Arrets</strong>
          <ul>
            <li v-for="stop in sortedStops(ligne.id)" :key="stop.id">{{ stop.order + 1 }}. {{ stop.name }}</li>
          </ul>
        </div>

        <div class="times">
          <strong>Departures</strong>
          <div class="chips">
            <span v-for="time in schedulesByLigne[ligne.id] || []" :key="time" class="chip">{{ time }}</span>
            <span v-if="!(schedulesByLigne[ligne.id] || []).length" class="chip muted">Aucun horaire</span>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<style scoped>
.schedule-page {
  min-height: 100vh;
  padding: 1rem;
  background: linear-gradient(145deg, #fff6e6, #eef8ff 40%, #ffffff);
}

.day-filter {
  margin: 1rem 0 1.2rem;
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 0.4rem;
}

.day-btn {
  border: 1px solid #d7c2a7;
  background: #fff;
  border-radius: 10px;
  padding: 0.45rem;
  cursor: pointer;
}

.day-btn.active {
  background: #d9711b;
  border-color: #d9711b;
  color: #fff;
}

.ligne-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.9rem;
}

.ligne-card {
  border: 1px solid #e1d8cb;
  border-radius: 14px;
  padding: 0.9rem;
  background: rgba(255, 255, 255, 0.9);
}

.stops ul {
  margin: 0.45rem 0 0;
  padding-left: 1rem;
}

.chips {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-top: 0.45rem;
}

.chip {
  background: #11304f;
  color: #fff;
  border-radius: 999px;
  padding: 0.22rem 0.65rem;
  font-size: 0.85rem;
}

.chip.muted,
.muted {
  background: #e8edf5;
  color: #42526b;
}

.error {
  color: #bf1336;
}

@media (min-width: 960px) {
  .schedule-page {
    padding: 2rem;
  }

  .ligne-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>

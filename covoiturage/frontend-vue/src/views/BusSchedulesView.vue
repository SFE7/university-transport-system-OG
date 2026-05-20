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
    <header class="page-header">
      <h1 class="page-title">Horaires des Bus</h1>
      <p class="page-subtitle">Choisissez un jour pour voir les passages prevus.</p>
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
      <article v-for="ligne in ligneStore.lignes" :key="ligne.id" class="glass ligne-card">
        <h3 class="section-title">{{ ligne.name }}</h3>
        <p class="line-desc">{{ ligne.description || 'Sans description' }}</p>

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
  padding: 40px 24px;
  max-width: 700px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 40px;
  text-align: center;
}

.page-title {
  margin: 0;
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  text-align: center;
}

.page-subtitle {
  margin-top: 8px;
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  text-align: center;
}

.day-filter {
  margin: 0 0 1.2rem;
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 0.4rem;
}

.day-btn {
  border: 1px solid rgba(253, 249, 240, 0.12);
  background: rgba(253, 249, 240, 0.06);
  color: rgba(253, 249, 240, 0.6);
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.day-btn.active {
  background: #ffe180;
  border-color: #ffe180;
  color: #1b3d2f;
  font-weight: 700;
}

.day-btn:hover:not(.active) {
  border-color: rgba(255, 225, 128, 0.4);
  color: #fdf9f0;
}

.ligne-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

.ligne-card {
  border-radius: 20px;
  padding: 24px;
}

.ligne-card p {
  color: rgba(253, 249, 240, 0.6);
}

.line-desc {
  margin-top: 8px;
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
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border: 1px solid rgba(255, 225, 128, 0.3);
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 12px;
}

.chip.muted,
.muted {
  background: rgba(253, 249, 240, 0.06);
  color: rgba(253, 249, 240, 0.4);
}

.error {
  color: #ff6b6b;
}

.section-title {
  color: #9f9065;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 16px;
}

@media (min-width: 960px) {
  .schedule-page {
    padding: 40px 24px;
  }

  .ligne-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 700px) {
  .day-filter {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}
</style>

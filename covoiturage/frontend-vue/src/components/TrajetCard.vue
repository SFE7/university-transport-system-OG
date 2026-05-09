<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Trajet } from '@/types'

const props = defineProps<{ trajet: Trajet }>()

const conducteurName = computed(() => props.trajet.conducteur?.name || 'Conducteur inconnu')
</script>

<template>
  <RouterLink class="card trajet-card" :to="`/trajets/${trajet.id}`">
    <div class="trajet-header">
      <div class="trajet-route">
        <h3 class="trajet-title">{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</h3>
        <p class="muted">Depart: {{ trajet.departure_time }}</p>
      </div>
      <span class="badge gray">{{ trajet.available_seats }} places</span>
    </div>
    <div class="trajet-meta">
      <div class="tag-list">
        <span class="badge">{{ trajet.status }}</span>
        <span class="badge dark" data-tip="Conducteur">{{ conducteurName }}</span>
      </div>
      <span class="hint">Voir details</span>
    </div>
  </RouterLink>
</template>

<style scoped>
.trajet-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 18px;
  transition: all 0.2s ease;
}

.trajet-card:hover {
  transform: translateY(-2px);
}

.trajet-card:active {
  transform: scale(0.98);
}

.trajet-title {
  margin: 0 0 6px;
}

.trajet-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.trajet-route {
  min-width: 0;
}

.trajet-route .muted {
  margin: 0;
}

.trajet-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: auto;
}

.hint {
  font-weight: 600;
  color: var(--accent);
  text-align: right;
}

[data-tip] {
  position: relative;
}

[data-tip]::after {
  content: attr(data-tip);
  position: absolute;
  left: 50%;
  bottom: 125%;
  transform: translateX(-50%);
  background: var(--ink);
  color: var(--bg);
  padding: 4px 8px;
  border-radius: 8px;
  font-size: 0.75rem;
  opacity: 0;
  pointer-events: none;
  transition: all 0.2s ease;
}

[data-tip]:hover::after {
  opacity: 1;
}

@media (max-width: 640px) {
  .trajet-header,
  .trajet-meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .hint {
    text-align: left;
  }
}
</style>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import type { Trajet } from '@/types'
import { getCarPhotoUrl } from '@/utils/carPhoto'

const props = defineProps<{ trajet: Trajet }>()
const showPhoto = ref(true)

const conducteurName = computed(() => props.trajet.conducteur?.name || 'Conducteur inconnu')

const formatDeparture = (value: string): string => {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return value
  }

  const months = [
    'Janvier',
    'Fevrier',
    'Mars',
    'Avril',
    'Mai',
    'Juin',
    'Juillet',
    'Aout',
    'Septembre',
    'Octobre',
    'Novembre',
    'Decembre',
  ]

  const day = String(date.getDate()).padStart(2, '0')
  const month = months[date.getMonth()]
  const year = date.getFullYear()
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return `${day} ${month} ${year} à ${hours}:${minutes}`
}

const departureLabel = computed(() => formatDeparture(props.trajet.departure_time))

const photoUrl = computed(
  () =>
    props.trajet.car_photo_url ??
    getCarPhotoUrl(props.trajet.car_category ?? '', props.trajet.car_model ?? '') ??
    null
)

watch(photoUrl, () => {
  showPhoto.value = true
})

function onPhotoError() {
  showPhoto.value = false
}

const statusMeta = computed(() => {
  switch (props.trajet.status) {
    case 'active':
      return { label: 'Active', className: 'status-active' }
    case 'full':
      return { label: 'Full', className: 'status-full' }
    case 'cancelled':
      return { label: 'Cancelled', className: 'status-cancelled' }
    case 'completed':
      return { label: 'Completed', className: 'status-completed' }
    default:
      return { label: props.trajet.status, className: 'status-active' }
  }
})
</script>

<template>
  <article class="glass trajet-card">
    <div class="trajet-main">
      <div class="trajet-line">
        <h3 class="trajet-title">{{ trajet.departure_point }} <span class="route-arrow">&rarr;</span> {{ trajet.arrival_point }}</h3>
      </div>
      <div class="trajet-meta">
        <p class="meta-item muted">Depart: {{ departureLabel }}</p>
        <p class="meta-item seat-info muted">
          <svg class="seat-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M7 10.5A2.5 2.5 0 0 1 9.5 8h5A2.5 2.5 0 0 1 17 10.5V12h1.5A2.5 2.5 0 0 1 21 14.5V18a1 1 0 0 1-2 0v-3.5a.5.5 0 0 0-.5-.5H17V18a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-4H5.5a.5.5 0 0 0-.5.5V18a1 1 0 1 1-2 0v-3.5A2.5 2.5 0 0 1 5.5 12H7v-1.5Z"
              fill="currentColor"
            />
          </svg>
          {{ trajet.available_seats }} places disponibles
        </p>
        <span class="status-pill" :class="statusMeta.className">{{ statusMeta.label }}</span>
        <p class="meta-item muted">Conducteur: {{ conducteurName }}</p>
        <p class="meta-item muted">
          Vehicule: {{ trajet.car_category || 'Categorie inconnue' }}
          <span v-if="trajet.car_model">- {{ trajet.car_model }}</span>
        </p>
      </div>
    </div>
    <div v-if="photoUrl && showPhoto" class="trajet-card-photo">
      <img :src="photoUrl" :alt="trajet.car_model || 'Photo vehicule'" @error="onPhotoError" />
    </div>
    <RouterLink class="details-btn" :to="`/trajets/${trajet.id}`">Voir détails</RouterLink>
  </article>
</template>

<style scoped>
.trajet-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  width: 100%;
  padding: 20px 24px;
  transition: all 0.2s ease;
  border-radius: 16px;
}

.trajet-card:hover {
  transform: translateY(-2px);
}

.trajet-main {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
  flex: 1;
}

.trajet-title {
  margin: 0;
  font-size: 1.06rem;
  line-height: 1.3;
  white-space: nowrap;
}

.trajet-line {
  min-width: 0;
}

.route-arrow {
  color: var(--accent);
  margin: 0 4px;
}

.trajet-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px 14px;
}

.meta-item {
  margin: 0;
  line-height: 1.35;
}

.seat-info {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.seat-icon {
  width: 16px;
  height: 16px;
  color: var(--accent);
  flex-shrink: 0;
}

.details-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  min-width: 120px;
  flex-shrink: 0;
  border-radius: 999px;
  padding: 10px 16px;
  font-weight: 600;
  color: #1b3d2f;
  background: #ffe180;
  border: 1px solid rgba(255, 225, 128, 0.2);
  box-shadow: 0 10px 20px rgba(255, 225, 128, 0.16);
  transition: all 0.2s ease;
}

.details-btn:hover {
  transform: translateY(-1px);
  background: #9f9065;
  color: #fdf9f0;
}

.details-btn:active {
  transform: scale(0.97);
}

.trajet-card-photo {
  width: 160px;
  min-width: 160px;
  height: 90px;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(253, 249, 240, 0.12);
  background: rgba(253, 249, 240, 0.05);
  flex-shrink: 0;
}

.trajet-card-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-active {
  background: rgba(70, 158, 92, 0.18);
  color: #2f8a50;
}

.status-full {
  background: rgba(191, 126, 37, 0.2);
  color: #b46f18;
}

.status-cancelled {
  background: rgba(191, 65, 65, 0.18);
  color: #c34646;
}

.status-completed {
  background: rgba(82, 128, 191, 0.2);
  color: #4f7cbc;
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

.status-active {
  background: rgba(100, 200, 120, 0.15);
  color: #6ec47a;
  border-color: rgba(100, 200, 120, 0.3);
}

.status-full {
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border-color: rgba(255, 225, 128, 0.3);
}

.status-cancelled {
  background: rgba(255, 107, 107, 0.15);
  color: #ff6b6b;
  border-color: rgba(255, 107, 107, 0.3);
}

.status-completed {
  background: rgba(253, 249, 240, 0.1);
  color: rgba(253, 249, 240, 0.82);
  border-color: rgba(253, 249, 240, 0.12);
}

@media (max-width: 640px) {
  .trajet-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }

  .trajet-card-photo {
    width: 100%;
    min-width: 0;
    height: 180px;
  }

  .trajet-title {
    white-space: normal;
  }

  .details-btn {
    width: 100%;
  }
}
</style>

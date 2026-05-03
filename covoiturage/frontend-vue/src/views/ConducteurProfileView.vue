<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAvisStore } from '@/stores/avisStore'
import { useAuthStore } from '@/stores/authStore'
import { useReservationStore } from '@/stores/reservationStore'

const route = useRoute()
const avisStore = useAvisStore()
const authStore = useAuthStore()
const reservationStore = useReservationStore()

const conducteurId = computed(() => Number(route.params.id))

const form = ref({
  rating: 5,
  comment: '',
  trajet_id: 0,
})

const feedback = ref('')

const averageRating = computed(() => {
  if (!avisStore.avisList.length) return 0
  const total = avisStore.avisList.reduce((sum, avis) => sum + avis.rating, 0)
  return Math.round((total / avisStore.avisList.length) * 10) / 10
})

const reviewableReservations = computed(() =>
  reservationStore.reservations.filter(
    (reservation) =>
      reservation.status === 'accepted' &&
      reservation.trajet?.status === 'completed' &&
      reservation.trajet?.conducteur?.id === conducteurId.value
  )
)

const canReview = computed(() =>
  authStore.isAuthenticated && reviewableReservations.value.length > 0
)

const handleSubmit = async () => {
  feedback.value = ''
  if (!form.value.trajet_id && reviewableReservations.value.length) {
    form.value.trajet_id = reviewableReservations.value[0]?.trajet_id ?? 0
  }
  try {
    await avisStore.create({
      conducteur_id: conducteurId.value,
      trajet_id: form.value.trajet_id,
      rating: form.value.rating,
      comment: form.value.comment || undefined,
    })
    feedback.value = 'Merci pour votre avis.'
    form.value.comment = ''
  } catch {
    feedback.value = avisStore.error || 'Avis failed'
  }
}

onMounted(() => {
  avisStore.fetchByConducteur(conducteurId.value)
  if (authStore.isAuthenticated) {
    reservationStore.fetchMyReservations()
  }
})
</script>

<template>
  <section class="page">
    <header>
      <h1 class="page-title">Profil conducteur</h1>
      <p class="subtitle">Conducteur #{{ conducteurId }}</p>
    </header>

    <div class="grid two">
      <div class="card">
        <h2>Note moyenne</h2>
        <p class="page-title">{{ averageRating }} / 5</p>
        <p class="muted">{{ avisStore.avisList.length }} avis</p>
      </div>
      <div class="card">
        <h2>Laisser un avis</h2>
        <p class="muted">Disponible apres un trajet termine.</p>
        <form v-if="canReview" class="form-row" @submit.prevent="handleSubmit">
          <label>
            Note (1-5)
            <input v-model.number="form.rating" type="number" min="1" max="5" class="input" />
          </label>
          <label v-if="reviewableReservations.length > 1">
            Trajet
            <select v-model.number="form.trajet_id" class="select">
              <option v-for="reservation in reviewableReservations" :key="reservation.id" :value="reservation.trajet_id">
                {{ reservation.trajet?.departure_point }} -> {{ reservation.trajet?.arrival_point }}
              </option>
            </select>
          </label>
          <label>
            Commentaire
            <textarea v-model="form.comment" class="textarea" rows="4"></textarea>
          </label>
          <button class="btn" type="submit">Envoyer</button>
          <p v-if="feedback" class="status">{{ feedback }}</p>
        </form>
        <p v-else class="muted">Vous ne pouvez pas laisser un avis pour ce conducteur.</p>
      </div>
    </div>

    <div class="card">
      <h2>Avis recus</h2>
      <div v-if="avisStore.isLoading" class="status">Chargement...</div>
      <div v-if="avisStore.error" class="status">{{ avisStore.error }}</div>
      <div v-for="avis in avisStore.avisList" :key="avis.id" class="card soft">
        <div class="tag-list">
          <span class="badge">{{ avis.rating }} / 5</span>
          <span class="badge gray">{{ avis.created_at }}</span>
        </div>
        <p class="muted">Par: {{ avis.reviewer?.name || 'Anonyme' }}</p>
        <p>{{ avis.comment || 'Aucun commentaire' }}</p>
      </div>
    </div>
  </section>
</template>

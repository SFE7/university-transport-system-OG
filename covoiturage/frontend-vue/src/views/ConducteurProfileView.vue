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

const editingAvisId = ref<number | null>(null)

const startEdit = (avis: any) => {
  if (!avis || !avis.reviewer) return
  editingAvisId.value = avis.id
  form.value.rating = avis.rating
  form.value.comment = avis.comment || ''
  form.value.trajet_id = avis.trajet_id || 0
}

const cancelEdit = () => {
  editingAvisId.value = null
  form.value.rating = 5
  form.value.comment = ''
  form.value.trajet_id = 0
}

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
    if (editingAvisId.value) {
      await avisStore.update(editingAvisId.value, {
        conducteur_id: conducteurId.value,
        trajet_id: form.value.trajet_id,
        rating: form.value.rating,
        comment: form.value.comment || undefined,
      })
      cancelEdit()
    } else {
      await avisStore.create({
        conducteur_id: conducteurId.value,
        trajet_id: form.value.trajet_id,
        rating: form.value.rating,
        comment: form.value.comment || undefined,
      })
    }
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

const confirmDelete = async (id: number) => {
  if (!window.confirm('Confirmer suppression de cet avis ?')) return
  await avisStore.delete(id)
}
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Profil conducteur</h1>
      <p class="subtitle">Conducteur #{{ conducteurId }}</p>
    </header>

    <div class="grid two">
      <div class="glass profile-card">
        <h2>Note moyenne</h2>
        <p class="rating-value">{{ averageRating }} / 5</p>
        <div class="star-row" aria-label="Evaluation moyenne">
          <span
            v-for="star in 5"
            :key="star"
            class="star"
            :class="star <= Math.round(averageRating) ? 'filled' : 'empty'"
          >
            ★
          </span>
        </div>
        <p class="muted">{{ avisStore.avisList.length }} avis</p>
      </div>
      <div class="glass profile-card">
        <h2>{{ editingAvisId ? 'Modifier votre avis' : 'Laisser un avis' }}</h2>
        <p class="muted">Disponible apres un trajet termine.</p>
        <form v-if="canReview" class="form-row" @submit.prevent="handleSubmit">
          <label class="field">
            <span class="field-label">Note (1-5)</span>
            <input v-model.number="form.rating" type="number" min="1" max="5" class="filter-input" />
          </label>
          <label v-if="reviewableReservations.length > 1" class="field">
            <span class="field-label">Trajet</span>
            <select v-model.number="form.trajet_id" class="filter-input">
              <option v-for="reservation in reviewableReservations" :key="reservation.id" :value="reservation.trajet_id">
                {{ reservation.trajet?.departure_point }} -> {{ reservation.trajet?.arrival_point }}
              </option>
            </select>
          </label>
          <label class="field">
            <span class="field-label">Commentaire</span>
            <textarea v-model="form.comment" class="filter-input textarea" rows="4"></textarea>
          </label>
          <div class="form-actions">
            <button class="primary-btn" type="submit">{{ editingAvisId ? 'Mettre a jour' : 'Envoyer' }}</button>
            <button v-if="editingAvisId" class="danger-btn" type="button" @click="cancelEdit">Annuler</button>
          </div>
          <p v-if="feedback" class="status">{{ feedback }}</p>
        </form>
        <p v-else class="muted">Vous ne pouvez pas laisser un avis pour ce conducteur.</p>
      </div>
    </div>

    <div class="glass reviews-card">
      <h2>Avis recus</h2>
      <div v-if="avisStore.isLoading" class="status">Chargement...</div>
      <div v-if="avisStore.error" class="status">{{ avisStore.error }}</div>
      <div v-if="avisStore.avisList.length" class="reviews-list">
        <div v-for="avis in avisStore.avisList" :key="avis.id" class="glass review-card">
          <div class="tag-list">
            <span class="status-pill pending">{{ avis.rating }} / 5</span>
            <span class="status-pill pending">{{ avis.created_at }}</span>
          </div>
          <div class="star-row" aria-label="Note de l'avis">
            <span v-for="star in 5" :key="star" class="star" :class="star <= avis.rating ? 'filled' : 'empty'">★</span>
          </div>
          <p class="muted">Par: {{ avis.reviewer?.name || 'Anonyme' }}</p>
          <p>{{ avis.comment || 'Aucun commentaire' }}</p>
          <div v-if="authStore.membre && avis.reviewer && authStore.membre.id === avis.reviewer.id" class="row-actions">
            <button class="primary-btn" @click="startEdit(avis)">Editer</button>
            <button class="danger-btn" @click="confirmDelete(avis.id)">Supprimer</button>
          </div>
        </div>
      </div>
      <div v-else class="empty-state">Aucun avis pour le moment.</div>
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

.profile-card,
.reviews-card,
.review-card {
  padding: 20px 24px;
  border-radius: 16px;
}

.profile-card,
.reviews-card {
  margin-bottom: 16px;
}

.rating-value {
  margin: 0;
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
}

.star-row {
  display: flex;
  gap: 6px;
  margin: 8px 0 12px;
}

.star {
  font-size: 18px;
}

.star.filled {
  color: #ffe180;
}

.star.empty {
  color: rgba(253, 249, 240, 0.2);
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

.filter-input:focus {
  outline: none;
  border-color: #ffe180;
}

.textarea {
  resize: vertical;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
  border-radius: 999px;
  padding: 10px 24px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
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

.status-pill.pending {
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border-color: rgba(255, 225, 128, 0.3);
}

.row-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.reviews-list {
  display: grid;
  gap: 16px;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}

@media (max-width: 640px) {
  .row-actions {
    flex-direction: column;
  }
}
</style>

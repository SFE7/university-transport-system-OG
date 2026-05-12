<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'
import { CAR_CATEGORIES } from '@/constants/carModels'

const OTHER_CATEGORY_OPTION = 'Autre / Autre modèle'
const OTHER_MODEL_OPTION = "Mon modèle n'est pas dans la liste"

const authStore = useAuthStore()
const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

onMounted(() => {
  trajetStore.fetchHistory()
  reservationStore.fetchMyReservations()
})

const myTrajets = computed(() => {
  if (Array.isArray(trajetStore.history)) {
    return trajetStore.history
  }

  const historyPayload = trajetStore.history as unknown as { data?: unknown }
  return Array.isArray(historyPayload?.data) ? historyPayload.data : []
})

const showForm = ref(false)
const formError = ref('')
const isSubmitting = ref(false)
const uploadedPhotoPreview = ref<string | null>(null)
const newTrajet = ref({
  departure_point: '',
  arrival_point: '',
  departure_time: '',
  available_seats: 1,
})

const categoryOptions = computed(() => [...Object.keys(CAR_CATEGORIES), OTHER_CATEGORY_OPTION])

const modelOptions = computed(() => {
  if (!trajetStore.selectedCategory) {
    return []
  }

  if (trajetStore.selectedCategory === OTHER_CATEGORY_OPTION) {
    return [OTHER_MODEL_OPTION]
  }

  const knownModels = CAR_CATEGORIES[trajetStore.selectedCategory] || []
  return [...knownModels, OTHER_MODEL_OPTION]
})

const isCustomModel = computed(() => trajetStore.selectedModel === OTHER_MODEL_OPTION)

const photoUrl = computed(() => {
  if (!trajetStore.selectedCategory || !trajetStore.selectedModel || isCustomModel.value) {
    return null
  }

  const category = encodeURIComponent(trajetStore.selectedCategory)
  const model = encodeURIComponent(trajetStore.selectedModel)
  return `/photos/${category}/${model}`
})

const resetCarPhotoStep = () => {
  trajetStore.carPhotoFile = null
  trajetStore.carPhotoUrl = null
  uploadedPhotoPreview.value = null
}

watch(
  () => trajetStore.selectedCategory,
  () => {
    trajetStore.selectedModel = ''
    resetCarPhotoStep()
  }
)

watch(
  () => trajetStore.selectedModel,
  () => {
    resetCarPhotoStep()
    if (!isCustomModel.value && photoUrl.value) {
      trajetStore.carPhotoUrl = photoUrl.value
    }
  }
)

const handleCarPhotoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] || null

  trajetStore.carPhotoFile = file
  trajetStore.carPhotoUrl = null
  uploadedPhotoPreview.value = file ? URL.createObjectURL(file) : null
}

const resetForm = () => {
  newTrajet.value = {
    departure_point: '',
    arrival_point: '',
    departure_time: '',
    available_seats: 1,
  }
  trajetStore.selectedCategory = ''
  trajetStore.selectedModel = ''
  resetCarPhotoStep()
}

const closeForm = () => {
  showForm.value = false
  formError.value = ''
  resetForm()
}

const submitTrajet = async () => {
  formError.value = ''
  if (
    !newTrajet.value.departure_point ||
    !newTrajet.value.arrival_point ||
    !newTrajet.value.departure_time
  ) {
    formError.value = 'Tous les champs sont obligatoires.'
    return
  }

  if (!trajetStore.selectedCategory || !trajetStore.selectedModel) {
    formError.value = 'Veuillez choisir une catégorie et un modèle de véhicule.'
    return
  }

  if (isCustomModel.value && !trajetStore.carPhotoFile) {
    formError.value = 'Veuillez soumettre une photo de votre véhicule.'
    return
  }

  if (!isCustomModel.value && !photoUrl.value) {
    formError.value = 'La photo existante est introuvable pour ce modele.'
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      ...newTrajet.value,
      car_category: trajetStore.selectedCategory,
      car_model: trajetStore.selectedModel,
      car_photo_url: trajetStore.carPhotoUrl,
      carPhotoFile: trajetStore.carPhotoFile,
    }

    console.log('[ConducteurDashboardView] submitting trajet payload', payload)

    await trajetStore.create(payload)
    closeForm()
    await trajetStore.fetchHistory()
  } catch (e) {
    formError.value = 'Erreur lors de la création du trajet.'
  } finally {
    isSubmitting.value = false
  }
}

const pendingReservations = computed(() =>
  Array.isArray(reservationStore.reservations)
    ? reservationStore.reservations.filter(
        (reservation) =>
          reservation.status === 'pending' && reservation.trajet?.status === 'active'
      )
    : []
)

const formatStatus = (s: unknown) => {
  const key = String(s || '').toLowerCase()
  const map: Record<string, string> = {
    active: 'Actif',
    full: 'Complet',
    cancelled: 'Annulé',
    pending: 'En attente',
  }
  return map[key] || String(s || '')
}

const formatDate = (v: unknown) => {
  try {
    const d = new Date(String(v || ''))
    if (isNaN(d.getTime())) return String(v || '')
    return d.toLocaleString()
  } catch {
    return String(v || '')
  }
}
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Espace conducteur</h1>
      <p class="subtitle">Gérez vos trajets, réservations et demandes de passagers.</p>
    </header>

    <div class="grid two">
      <section class="glass dashboard-card">
        <h2 class="section-title">Mes trajets</h2>
        <div v-if="showForm" class="glass form-panel">
          <h3 class="subsection-title">Publier un trajet</h3>
          <div class="form-grid">
            <input v-model="newTrajet.departure_point" class="field-input" placeholder="Point de départ" />
            <input v-model="newTrajet.arrival_point" class="field-input" placeholder="Point d'arrivée" />
            <input v-model="newTrajet.departure_time" class="field-input" type="datetime-local" />
            <input v-model.number="newTrajet.available_seats" class="field-input" type="number" min="1" placeholder="Places disponibles" />
            <label class="field-label" for="car-category">Catégorie du véhicule</label>
            <select id="car-category" v-model="trajetStore.selectedCategory" class="field-input">
              <option disabled value="">Sélectionner une catégorie</option>
              <option v-for="category in categoryOptions" :key="category" :value="category">
                {{ category }}
              </option>
            </select>

            <template v-if="trajetStore.selectedCategory">
              <label class="field-label" for="car-model">Modèle du véhicule</label>
              <select id="car-model" v-model="trajetStore.selectedModel" class="field-input">
                <option disabled value="">Sélectionner un modèle</option>
                <option v-for="model in modelOptions" :key="model" :value="model">
                  {{ model }}
                </option>
              </select>
            </template>

            <template v-if="trajetStore.selectedModel">
              <div v-if="!isCustomModel && photoUrl" class="photo-step">
                <img :src="photoUrl" alt="Photo du véhicule" class="photo-thumb" />
                <p class="muted">Photo existante utilisée</p>
              </div>

              <div v-else class="photo-step">
                <label class="field-label" for="car-photo-upload">
                  Veuillez soumettre une photo de votre véhicule.
                </label>
                <input
                  id="car-photo-upload"
                  class="field-input"
                  type="file"
                  accept="image/png"
                  required
                  @change="handleCarPhotoUpload"
                />
                <img
                  v-if="uploadedPhotoPreview"
                  :src="uploadedPhotoPreview"
                  alt="Aperçu de la photo soumise"
                  class="photo-thumb"
                />
              </div>
            </template>

            <div class="button-row">
              <button class="primary-btn" type="button" :disabled="isSubmitting" @click="submitTrajet">
                {{ isSubmitting ? 'Publication en cours...' : 'Publier le trajet' }}
              </button>
              <button class="ghost-btn" type="button" :disabled="isSubmitting" @click="closeForm">Annuler</button>
            </div>
            <p v-if="formError" class="error-message">{{ formError }}</p>
          </div>
        </div>

        <button v-else class="primary-btn new-trajet-btn" type="button" @click="showForm = true">
          + Publier un trajet
        </button>
        <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
        <div v-if="myTrajets.length" class="items-list">
          <article v-for="trajet in myTrajets" :key="trajet.id" class="glass item-card">
          <div class="tag-list">
            <span
              class="status-pill"
              :class="{
                active: String(trajet.status) === 'active',
                pending: String(trajet.status) === 'full',
                cancelled: String(trajet.status) === 'cancelled',
              }"
            >
              {{ formatStatus(trajet.status) }}
            </span>
            <span class="status-pill pending">{{ formatDate(trajet.departure_time) }}</span>
          </div>
          <p class="route-text">{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
          <div class="button-row">
            <button
              v-if="String(trajet.status) === 'active'"
              class="danger-btn"
              type="button"
              @click="trajetStore.cancel(trajet.id)"
            >
              Annuler
            </button>
          </div>
          </article>
        </div>
        <p v-if="!myTrajets.length" class="empty-state">Vous n'avez pas encore publié de trajet.</p>
      </section>

      <section class="glass dashboard-card">
        <h2 class="section-title">Reservations en attente</h2>
        <div v-if="reservationStore.isLoading" class="status">Chargement...</div>
        <div v-if="pendingReservations.length" class="items-list">
          <article v-for="reservation in pendingReservations" :key="reservation.id" class="glass item-card">
            <p class="muted">Passager: {{ reservation.membre_id }}</p>
            <p class="muted">Demande: {{ formatDate(reservation.created_at) }}</p>
            <div class="button-row">
              <button class="accept-btn" type="button" @click="reservationStore.accept(reservation.id)">
              Accepter
              </button>
              <button class="danger-btn" type="button" @click="reservationStore.refuse(reservation.id)">
              Refuser
              </button>
            </div>
          </article>
        </div>
        <p v-if="!pendingReservations.length" class="empty-state">Aucune demande en attente pour le moment.</p>
      </section>
    </div>

    <hr class="divider" />

    <p v-if="!authStore.isConducteur" class="status">Accès réservé aux conducteurs.</p>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  font-size: 28px;
  margin: 0 0 6px;
  color: #fdf9f0;
}

.subtitle {
  margin: 0;
  color: rgba(253, 249, 240, 0.72);
  font-size: 14px;
}

.dashboard-card {
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 16px;
}

.dashboard-card:hover,
.item-card:hover,
.form-panel:hover {
  border-color: rgba(255, 225, 128, 0.5);
}

.section-title {
  color: #6c757d;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin: 0 0 16px;
}

.subsection-title {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 600;
  color: #fdf9f0;
}

.form-panel {
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 20px;
}

.form-grid {
  display: grid;
  gap: 12px;
}

.field-input {
  width: 100%;
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 10px 14px;
  font-family: inherit;
  font-size: 14px;
}

.field-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.field-input:focus {
  outline: none;
  border-color: #ffe180;
}

.field-label {
  font-size: 13px;
  color: rgba(253, 249, 240, 0.72);
}

.photo-step {
  display: grid;
  gap: 10px;
  margin-top: 4px;
}

.photo-thumb {
  max-height: 150px;
  width: auto;
  border-radius: 12px;
  border: 1px solid rgba(253, 249, 240, 0.2);
  background: rgba(253, 249, 240, 0.04);
}

.error-message {
  background-color: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.5);
  color: #fca5a5;
  padding: 12px;
  border-radius: 8px;
  margin-top: 16px;
  font-size: 14px;
}

.button-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.primary-btn,
.accept-btn,
.danger-btn,
.ghost-btn {
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.primary-btn,
.new-trajet-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
}

.primary-btn:hover,
.new-trajet-btn:hover {
  background: #e6b800;
  color: #0f2a1f;
}

.primary-btn:disabled,
.ghost-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.accept-btn {
  background: rgba(100, 200, 120, 0.15);
  border: 1px solid rgba(100, 200, 120, 0.3);
  color: #6ec47a;
  padding: 8px 20px;
}

.accept-btn:hover {
  background: rgba(100, 200, 120, 0.3);
}

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
  padding: 8px 20px;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.ghost-btn {
  background: transparent;
  border: 1px solid rgba(253, 249, 240, 0.15);
  color: #fdf9f0;
  padding: 10px 24px;
}

.ghost-btn:hover {
  border-color: rgba(255, 225, 128, 0.35);
}

.items-list {
  display: grid;
  gap: 16px;
}

.item-card {
  padding: 20px 24px;
  border-radius: 16px;
  background: rgba(255,255,255,0.02);
  box-shadow: 0 6px 18px rgba(0,0,0,0.35);
}

.route-text {
  margin: 12px 0 0;
  font-weight: 600;
  color: #fdf9f0;
}

.empty-state {
  color: rgba(253, 249, 240, 0.5);
  text-align: center;
  padding: 40px 0;
  font-size: 15px;
}

.muted {
  color: rgba(253,249,240,0.6);
  font-size: 13px;
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

.status-pill.active {
  background: rgba(100, 200, 120, 0.15);
  color: #6ec47a;
  border-color: rgba(100, 200, 120, 0.3);
}

.status-pill.pending {
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border-color: rgba(255, 225, 128, 0.3);
}

.status-pill.cancelled {
  background: rgba(255, 107, 107, 0.15);
  color: #ff6b6b;
  border-color: rgba(255, 107, 107, 0.3);
}

.divider {
  border: none;
  border-top: 1px solid rgba(253, 249, 240, 0.08);
  margin: 24px 0;
}

@media (max-width: 860px) {
  .grid.two {
    grid-template-columns: 1fr;
  }
}
</style>

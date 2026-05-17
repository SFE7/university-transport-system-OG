<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { CAR_CATEGORIES } from '@/constants/carModels'
import trajetService from '@/services/trajetService'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'

const OTHER_CATEGORY_OPTION = 'Autre / Autre modèle'
const OTHER_MODEL_OPTION = "Mon modèle n'est pas dans la liste"

const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

const selectedTrajetId = ref<number | null>(null)
const actionError = ref('')
const showForm = ref(false)
const formError = ref('')
const isSubmitting = ref(false)
const staticPhotoFailed = ref(false)
const uploadedPhotoPreview = ref<string | null>(null)
const myTrajetsWithReservations = ref<any[]>([])
const newTrajet = ref({
  departure_point: '',
  arrival_point: '',
  departure_time: '',
  available_seats: 1,
})

const mesTrajets = computed(() => trajetStore.myTrajets)
const demandes = computed(() => reservationStore.pendingDemandes)
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
  staticPhotoFailed.value = false
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

  isSubmitting.value = true
  try {
    const payload = {
      ...newTrajet.value,
      car_category: trajetStore.selectedCategory,
      car_model: trajetStore.selectedModel,
      car_photo_url: trajetStore.carPhotoUrl,
      carPhotoFile: trajetStore.carPhotoFile,
    }

    await trajetStore.create(payload)
    closeForm()
    await trajetStore.fetchMy()
  } catch {
    formError.value = 'Erreur lors de la création du trajet.'
  } finally {
    isSubmitting.value = false
  }
}

const filteredDemandes = computed(() => {
  if (!selectedTrajetId.value) {
    return demandes.value
  }
  return demandes.value.filter((reservation) => reservation.trajet_id === selectedTrajetId.value)
})

const acceptedReservations = computed(() => {
  return myTrajetsWithReservations.value.flatMap((trajet: any) => {
    if (trajet.status !== 'active' || !Array.isArray(trajet.reservations)) {
      return []
    }

    return trajet.reservations
      .filter((reservation: any) => reservation.status === 'accepted')
      .map((reservation: any) => ({
        ...reservation,
        trajet,
      }))
  })
})

const formatDate = (value: unknown) => {
  const date = new Date(String(value || ''))
  if (isNaN(date.getTime())) {
    return String(value || '')
  }
  return date.toLocaleString()
}

const cancelAccepted = async (reservationId: number) => {
  actionError.value = ''
  try {
    await reservationStore.cancel(reservationId)
    await reservationStore.fetchMyDemandes()
  } catch (error: any) {
    actionError.value = error?.response?.data?.message || 'Impossible d\'annuler la réservation.'
  }
}

const selectTrajet = (trajetId: number) => {
  selectedTrajetId.value = selectedTrajetId.value === trajetId ? null : trajetId
}

const handleDecision = async (reservationId: number, action: 'accept' | 'refuse') => {
  actionError.value = ''
  try {
    if (action === 'accept') {
      await reservationStore.accept(reservationId)
    } else {
      await reservationStore.refuse(reservationId)
    }
  } catch (error: any) {
    actionError.value = error?.response?.data?.message || 'Action impossible pour cette demande.'
  }
}

onMounted(async () => {
  await Promise.all([
    trajetStore.fetchMy(),
    reservationStore.fetchMyDemandes(),
  ])

  const trajetDetails = await Promise.all(
    mesTrajets.value.map(async (trajet: any) => {
      const response = await trajetService.getOne(trajet.id)
      return response.data
    })
  )

  myTrajetsWithReservations.value = trajetDetails as any[]
})
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Tableau conducteur</h1>
      <p class="subtitle">Créez vos trajets et traitez les demandes de reservation.</p>
    </header>

    <div class="grid two">
      <section class="glass dashboard-card">
        <h2 class="section-title">Mes Trajets</h2>

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
              <div v-if="!isCustomModel && photoUrl && !staticPhotoFailed" class="photo-step">
                <img :src="photoUrl" alt="Photo du véhicule" class="photo-thumb" @error="staticPhotoFailed = true" />
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

        <div v-if="trajetStore.isLoading" class="status">Chargement des trajets...</div>
        <p v-else-if="!mesTrajets.length" class="empty-state">Vous n'avez pas encore de trajets.</p>

        <div v-else class="items-list">
          <article v-for="trajet in mesTrajets" :key="trajet.id" class="glass item-card">
            <p class="route-text">{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
            <p class="muted">Depart: {{ formatDate(trajet.departure_time) }}</p>
            <p class="muted">Places: {{ trajet.available_seats }}</p>
            <p class="muted">Statut: {{ trajet.status }}</p>
            <button class="primary-btn" type="button" @click="selectTrajet(trajet.id)">
              {{ selectedTrajetId === trajet.id ? 'Masquer demandes' : 'Voir demandes' }}
            </button>
          </article>
        </div>
      </section>

      <section class="glass dashboard-card">
        <h2 class="section-title">Demandes de reservation</h2>
        <p v-if="selectedTrajetId" class="muted">Filtre actif: trajet #{{ selectedTrajetId }}</p>

        <div v-if="reservationStore.isLoading" class="status">Chargement des demandes...</div>
        <p v-else-if="!filteredDemandes.length" class="empty-state">Aucune demande en attente.</p>

        <div v-else class="items-list">
          <article v-for="reservation in filteredDemandes" :key="reservation.id" class="glass item-card">
            <p class="muted">Membre: {{ reservation.membre?.name || `#${reservation.membre_id}` }}</p>
            <p class="muted">
              Trajet: {{ reservation.trajet?.departure_point || '-' }} -> {{ reservation.trajet?.arrival_point || '-' }}
            </p>
            <p class="muted">Demandee le: {{ formatDate(reservation.created_at) }}</p>

            <div class="button-row">
              <button class="accept-btn" type="button" @click="handleDecision(reservation.id, 'accept')">
                Accepter
              </button>
              <button class="danger-btn" type="button" @click="handleDecision(reservation.id, 'refuse')">
                Refuser
              </button>
            </div>
          </article>
        </div>

        <p v-if="actionError" class="error-message">{{ actionError }}</p>

        <h3 class="subsection-title">Réservations acceptées</h3>
        <div v-if="reservationStore.isLoading" class="status">Chargement des réservations...</div>
        <p v-else-if="!acceptedReservations.length" class="empty-state">Aucune réservation acceptée pour le moment.</p>

        <div v-else class="items-list">
          <article v-for="reservation in acceptedReservations" :key="reservation.id" class="glass item-card">
            <p class="muted">Membre: {{ reservation.membre?.name || `#${reservation.membre_id}` }}</p>
            <p class="muted">Départ: {{ formatDate(reservation.trajet?.departure_time) }}</p>

            <div class="button-row">
              <button class="danger-btn" type="button" @click="cancelAccepted(reservation.id)">Annuler</button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 30px;
  margin: 0 0 8px;
  color: #fdf9f0;
}

.subtitle {
  margin: 0;
  color: rgba(253, 249, 240, 0.72);
}

.dashboard-card {
  padding: 24px;
  border-radius: 16px;
}

.form-panel {
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 20px;
}

.subsection-title {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 600;
  color: #fdf9f0;
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
  width: 100%;
  max-width: 260px;
  border-radius: 12px;
  border: 1px solid rgba(253, 249, 240, 0.15);
}

.new-trajet-btn {
  margin: 12px 0 18px 0;
  display: inline-block;
}

select.field-input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background: rgba(253, 249, 240, 0.07);
  color: #fdf9f0;
  border: 1px solid rgba(253, 249, 240, 0.15);
  padding: 10px 14px;
  border-radius: 12px;
}

select.field-input option {
  color: #1b3d2f;
}

.ghost-btn {
  background: transparent;
  color: #fdf9f0;
  border: 1px solid rgba(253, 249, 240, 0.12);
  padding: 9px 16px;
  border-radius: 999px;
  cursor: pointer;
}

.section-title {
  color: #ffe180;
  font-size: 14px;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  margin: 0 0 16px;
}

.items-list {
  display: grid;
  gap: 14px;
}

.item-card {
  padding: 18px;
  border-radius: 14px;
}

.route-text {
  margin: 0 0 8px;
  font-weight: 700;
  color: #fdf9f0;
}

.muted {
  margin: 4px 0;
  color: rgba(253, 249, 240, 0.7);
  font-size: 13px;
}

.button-row {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.primary-btn,
.accept-btn,
.danger-btn {
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 9px 16px;
  cursor: pointer;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
}

.accept-btn {
  background: rgba(110, 196, 122, 0.2);
  color: #6ec47a;
  border: 1px solid rgba(110, 196, 122, 0.4);
}

.danger-btn {
  background: rgba(255, 107, 107, 0.18);
  color: #ff6b6b;
  border: 1px solid rgba(255, 107, 107, 0.45);
}

.error-message {
  margin-top: 12px;
  color: #ff6b6b;
}

.empty-state,
.status {
  color: rgba(253, 249, 240, 0.6);
}

@media (max-width: 900px) {
  .grid.two {
    grid-template-columns: 1fr;
  }
}
</style>

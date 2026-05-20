<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAvisStore } from '@/stores/avisStore'
import { useAuthStore } from '@/stores/authStore'
import { useSignalementStore } from '@/stores/signalementStore'
import { useTrajetStore } from '@/stores/trajetStore'
import { useReservationStore } from '@/stores/reservationStore'
import { getCarPhotoUrl } from '@/utils/carPhoto'

const route = useRoute()
const authStore = useAuthStore()
const avisStore = useAvisStore()
const signalementStore = useSignalementStore()
const trajetStore = useTrajetStore()
const reservationStore = useReservationStore()

const actionMessage = ref('')
const actionError = ref('')
const reportOpen = ref(false)
const reportMessage = ref('')
const reportError = ref('')
const isReporting = ref(false)
const reportForm = reactive({ raison: '', description: '' })

const trajetId = computed(() => Number(route.params.id))

const starsFor = (note: number) => '★'.repeat(note) + '☆'.repeat(5 - note)

const normalizeAvisList = (raw: any) => {
  if (!raw) return []
  // Handle either an array or a paginated response with a `data` array
  let list = Array.isArray(raw) ? raw : raw.data ?? raw
  if (!Array.isArray(list)) return []
  return list.map((a: any) => ({
    id: a.id ?? a._id ?? null,
    note: Number(a.note ?? a.rating ?? 0),
    commentaire: a.commentaire ?? a.comment ?? a.content ?? '',
    trajet_id: a.trajet_id ?? a.trajet?.id ?? null,
    membre_id: a.membre_id ?? a.reviewer_id ?? a.user_id ?? null,
    conducteur_id: a.conducteur_id ?? a.driver_id ?? (a.trajet?.conducteur_id ?? null),
  }))
}

const displayedAvis = computed(() => normalizeAvisList(avisStore.avis))

const photoUrl = computed(() => {
  const trajet = trajetStore.currentTrajet
  if (!trajet) {
    return null
  }

  return (
    trajet.car_photo_url ??
    getCarPhotoUrl(trajet.car_category ?? '', trajet.car_model ?? '') ??
    null
  )
})

const canReserve = computed(() => {
  const trajet = trajetStore.currentTrajet
  return (
    authStore.isAuthenticated &&
    !authStore.isConducteur &&
    trajet?.status === 'active' &&
    Number(trajet?.available_seats ?? 0) > 0
  )
})

const canReport = computed(() => {
  const trajet = trajetStore.currentTrajet
  return authStore.isAuthenticated && authStore.membre?.role === 'membre' && Boolean(trajet?.conducteur?.id)
})

const openReportModal = () => {
  reportMessage.value = ''
  reportError.value = ''
  reportOpen.value = true
}

const closeReportModal = () => {
  reportOpen.value = false
}

const handleReserve = async () => {
  actionMessage.value = ''
  actionError.value = ''
  try {
    await reservationStore.create({ trajet_id: trajetId.value })
    actionMessage.value = 'Reservation envoyee.'
  } catch {
    actionError.value = reservationStore.error || 'Reservation failed'
  }
}

const handleReport = async () => {
  const trajet = trajetStore.currentTrajet
  if (!trajet?.conducteur?.id) {
    reportError.value = 'Conducteur introuvable.'
    return
  }

  reportMessage.value = ''
  reportError.value = ''
  isReporting.value = true

  try {
    await signalementStore.create({
      conducteur_id: trajet.conducteur.id,
      trajet_id: trajet.id,
      raison: reportForm.raison,
      description: reportForm.description || null,
    })
    reportMessage.value = 'Signalement envoyé'
    reportForm.raison = ''
    reportForm.description = ''
    reportOpen.value = false
  } catch (err: any) {
    reportError.value = signalementStore.error || err?.response?.data?.message || 'Signalement failed'
  } finally {
    isReporting.value = false
  }
}

onMounted(() => {
  trajetStore.fetchOne(trajetId.value)
})

watch(
  () => trajetStore.currentTrajet,
  (trajet) => {
    if (trajet?.conducteur?.id) {
      avisStore.fetchByConducteur(trajet.conducteur.id)
    }
  },
  { immediate: true },
)
</script>

<template>
  <section class="page page-shell">
    <div v-if="trajetStore.currentTrajet" class="glass detail-card">
      <div class="trajet-header">
        <div>
          <h1 class="page-title">
            {{ trajetStore.currentTrajet.departure_point }} ->
            {{ trajetStore.currentTrajet.arrival_point }}
          </h1>
          <p class="subtitle">Depart: {{ trajetStore.currentTrajet.departure_time }}</p>
        </div>
        <div class="tag-list">
          <span
            class="status-pill"
            :class="{
              active:
                String(trajetStore.currentTrajet.status) === 'active' ||
                String(trajetStore.currentTrajet.status) === 'accepted',
              pending:
                String(trajetStore.currentTrajet.status) === 'full' ||
                String(trajetStore.currentTrajet.status) === 'pending',
              cancelled:
                String(trajetStore.currentTrajet.status) === 'cancelled' ||
                String(trajetStore.currentTrajet.status) === 'refused',
            }"
          >
            {{ trajetStore.currentTrajet.status }}
          </span>
          <span class="status-pill pending">{{ trajetStore.currentTrajet.available_seats }} places</span>
        </div>
      </div>
      <div v-if="photoUrl" class="detail-photo">
        <img :src="photoUrl" :alt="trajetStore.currentTrajet.car_model || 'Photo vehicule'" />
      </div>
      <div class="divider"></div>
      <div class="grid two">
        <div>
          <h3>Conducteur</h3>
          <p class="muted">
            {{ trajetStore.currentTrajet.conducteur?.name || 'Conducteur inconnu' }}
          </p>
        </div>
        <div>
          <h3>Membre ID</h3>
          <p class="muted">{{ trajetStore.currentTrajet.membre_id }}</p>
        </div>
        <div>
          <h3>Vehicule</h3>
          <p class="muted">
            {{ trajetStore.currentTrajet.car_category || 'Categorie inconnue' }}
            <span v-if="trajetStore.currentTrajet.car_model">
              - {{ trajetStore.currentTrajet.car_model }}
            </span>
          </p>
        </div>
        <div>
          <h3>Photo du vehicule</h3>
          <p class="muted">{{ trajetStore.currentTrajet.car_photo_url ? 'Disponible' : 'Aucune photo' }}</p>
        </div>
      </div>
      <div class="form-actions">
        <button v-if="canReserve" class="primary-btn" type="button" @click="handleReserve">
          Reserver
        </button>
        <button v-if="canReport" class="secondary-btn" type="button" @click="openReportModal">
          Signaler ce conducteur
        </button>
        <span v-if="!canReserve && !canReport" class="status">Connexion requise, trajet indisponible, ou plus de places disponibles.</span>
      </div>
      <p v-if="actionMessage" class="success-message">{{ actionMessage }}</p>
      <p v-if="actionError" class="error-message">{{ actionError }}</p>
      <p v-if="reportMessage" class="success-message">{{ reportMessage }}</p>
      <p v-if="reportError" class="error-message">{{ reportError }}</p>

      <div class="divider"></div>
      <section class="avis-list-section">
        <h3 class="section-label">Avis sur le conducteur</h3>
        <p v-if="avisStore.isLoading" class="muted">Chargement des avis...</p>
        <p v-else-if="!displayedAvis.length" class="muted">Aucun avis pour ce conducteur.</p>
        <div v-else class="avis-list">
          <div v-for="avis in displayedAvis" :key="avis.id" class="glass avis-card">
            <span class="stars">{{ starsFor(avis.note) }}</span>
            <p class="muted avis-comment">{{ avis.commentaire }}</p>
            <p class="muted avis-meta">Trajet #{{ avis.trajet_id }}</p>
          </div>
        </div>
      </section>

      <div v-if="reportOpen" class="modal-backdrop" @click.self="closeReportModal">
        <div class="report-modal">
          <div class="modal-header">
            <h3>Signalement</h3>
            <button type="button" class="ghost-close" @click="closeReportModal">×</button>
          </div>
          <form class="report-form" @submit.prevent="handleReport">
            <label>
              <span>Raison</span>
              <input v-model.trim="reportForm.raison" type="text" minlength="10" required placeholder="Décrivez brièvement le problème" />
            </label>
            <label>
              <span>Description</span>
              <textarea v-model.trim="reportForm.description" rows="4" placeholder="Détails complémentaires (optionnel)"></textarea>
            </label>
            <div class="form-actions modal-actions">
              <button type="button" class="ghost-btn" @click="closeReportModal">Annuler</button>
              <button type="submit" class="primary-btn" :disabled="isReporting">
                {{ isReporting ? 'Envoi...' : 'Envoyer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">Chargement du trajet...</div>
  </section>
</template>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 900px;
  margin: 0 auto;
}

.trajet-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.detail-card {
  padding: 20px 24px;
  border-radius: 16px;
}

.detail-photo {
  margin: 18px 0;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(253, 249, 240, 0.12);
  background: rgba(253, 249, 240, 0.05);
}

.detail-photo img {
  display: block;
  width: 100%;
  max-height: 260px;
  object-fit: cover;
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
  margin-right: 8px;
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

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.secondary-btn {
  background: transparent;
  color: #ffe180;
  border: 1px solid rgba(255, 225, 128, 0.35);
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
}

.secondary-btn:hover {
  background: rgba(255, 225, 128, 0.12);
}

.primary-btn:disabled,
.secondary-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(11, 20, 16, 0.72);
  backdrop-filter: blur(6px);
  padding: 20px;
  z-index: 30;
}

.report-modal {
  width: min(100%, 520px);
  border-radius: 20px;
  padding: 20px;
  background: linear-gradient(180deg, rgba(27, 61, 47, 0.98), rgba(15, 30, 24, 0.98));
  border: 1px solid rgba(255, 225, 128, 0.18);
  box-shadow: 0 24px 72px rgba(0, 0, 0, 0.35);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}

.ghost-close {
  background: transparent;
  border: none;
  color: #fdf9f0;
  font-size: 26px;
  line-height: 1;
  padding: 0;
}

.report-form {
  display: grid;
  gap: 14px;
}

.report-form label {
  display: grid;
  gap: 8px;
  color: #fdf9f0;
  font-size: 14px;
}

.report-form input,
.report-form textarea {
  width: 100%;
  border-radius: 14px;
  border: 1px solid rgba(255, 225, 128, 0.2);
  background: rgba(253, 249, 240, 0.06);
  color: #fdf9f0;
  padding: 12px 14px;
}

.report-form textarea {
  resize: vertical;
}

/* Improved avis styling */
.avis-list {
  display: grid;
  gap: 12px;
  margin-top: 12px;
}

.avis-card {
  padding: 14px 16px;
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.04);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stars {
  color: #ffd166;
  font-size: 18px;
  font-weight: 300; /* thinner stars */
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  letter-spacing: 1px;
}

.avis-comment {
  margin: 0;
  color: #f1f1f1;
  font-size: 15px;
  line-height: 1.4;
}

.avis-meta {
  font-size: 12px;
  color: rgba(255,255,255,0.65);
}

.section-label {
  margin-bottom: 8px;
  font-weight: 800;
  color: #fdf9f0;
}

@media (max-width: 600px) {
  .detail-card {
    padding: 14px;
  }
  .stars {
    font-size: 16px;
  }
}

.modal-actions {
  justify-content: flex-end;
}

.success-message {
  color: #6ec47a;
  font-size: 13px;
  margin: 0;
}

.error-message {
  color: #ff6b6b;
  font-size: 13px;
  margin: 0;
}

.avis-list-section {
  margin-top: 8px;
}

.section-label {
  font-size: 13px;
  font-weight: 700;
  color: rgba(253, 249, 240, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin: 0 0 12px;
}

.avis-list {
  display: grid;
  gap: 12px;
}

.avis-card {
  padding: 16px 20px;
  border-radius: 14px;
  display: grid;
  gap: 6px;
}

.stars {
  font-size: 16px;
  color: #ffe180;
}

.avis-comment {
  font-size: 14px;
  margin: 0;
}

.avis-meta {
  font-size: 12px;
  margin: 0;
  opacity: 0.5;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}

@media (max-width: 640px) {
  .trajet-header {
    flex-direction: column;
  }
}
</style>

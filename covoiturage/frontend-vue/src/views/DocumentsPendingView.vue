<template>
  <section class="pending-documents-page">
    <div class="hero-card">
      <div class="hero-copy">
        <span class="eyebrow">Documents</span>
        <h1>Vérification en attente</h1>
        <p>
          Votre compte est en cours de contrôle. Vous pouvez consulter l’état de vos documents et téléverser les pièces manquantes.
        </p>
      </div>

      <div class="status-pill">
        <span class="status-dot"></span>
        En attente de vérification
      </div>
    </div>

    <div v-if="routeMessage" class="info-banner">
      {{ routeMessage }}
    </div>

    <div v-if="loading" class="state-card">Chargement de vos documents...</div>
    <div v-else-if="error" class="state-card error">{{ error }}</div>

    <template v-else-if="status">
      <div class="summary-grid">
        <article class="summary-card">
          <span class="summary-label">Statut</span>
          <strong>{{ status.has_verified_documents ? 'Vérifié' : 'En attente de vérification' }}</strong>
        </article>
        <article class="summary-card">
          <span class="summary-label">Rôle</span>
          <strong>{{ status.role }}</strong>
        </article>
        <article class="summary-card">
          <span class="summary-label">Documents requis</span>
          <strong>{{ requiredDocuments.length }}</strong>
        </article>
      </div>

      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>Vos documents</h2>
            <p>Les éléments approuvés apparaissent ci-dessous. Les pièces refusées ou manquantes peuvent être renvoyées.</p>
          </div>
        </div>

        <div v-if="documents.length === 0" class="empty-note">
          Aucun document soumis pour le moment.
        </div>

        <article v-for="doc in documents" :key="doc.id" class="document-row">
          <div>
            <div class="row-top">
              <h3>{{ documentLabels[doc.type] }}</h3>
              <span class="badge" :class="badgeClass(doc.status)">{{ statusLabels[doc.status] }}</span>
            </div>
            <p class="muted">Téléversé le {{ formatDate(doc.created_at) }}</p>
            <p v-if="doc.rejection_reason" class="rejection">{{ doc.rejection_reason }}</p>
          </div>
        </article>
      </div>

      <form class="panel upload-panel" @submit.prevent="submitDocuments">
        <div class="panel-head">
          <div>
            <h2>Compléter le dossier</h2>
            <p>Sélectionnez uniquement les pièces manquantes ou refusées.</p>
          </div>
          <span class="badge pending">En attente de vérification</span>
        </div>

        <div v-if="requiredDocuments.length === 0" class="empty-note">
          Aucun document supplémentaire n’est requis pour ce compte.
        </div>

        <div v-else class="upload-grid">
          <label v-for="type in requiredDocuments" :key="type" class="upload-card">
            <div class="row-top">
              <span class="upload-title">{{ documentLabels[type] }}</span>
              <span v-if="documentsByType[type]?.status === 'approuve'" class="badge approved">Approuvé</span>
              <span v-else-if="documentsByType[type]?.status === 'rejete'" class="badge rejected">Refusé</span>
              <span v-else class="badge pending">À téléverser</span>
            </div>

            <p class="muted">
              <template v-if="documentsByType[type]?.status === 'approuve'">
                Cette pièce a déjà été validée.
              </template>
              <template v-else-if="documentsByType[type]?.status === 'rejete'">
                Ajoutez une nouvelle version pour relancer la vérification.
              </template>
              <template v-else>
                Fournissez le document demandé pour terminer la vérification.
              </template>
            </p>

            <input
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              class="file-input"
              :disabled="documentsByType[type]?.status === 'approuve'"
              @change="onFileChange(type, $event)"
            />

            <p v-if="selectedFiles[type]" class="selected-file">{{ selectedFiles[type]?.name }}</p>
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="submit-btn" :disabled="submitting || !canSubmit">
            {{ submitting ? 'Envoi en cours...' : 'Envoyer les documents' }}
          </button>
        </div>

        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
      </form>
    </template>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import documentService from '@/services/documentService'
import type { DocumentSoumis, DocumentStatusResponse } from '@/types/admin'

type DocumentType = DocumentSoumis['type']

const authStore = useAuthStore()
const route = useRoute()

const loading = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)
const successMessage = ref('')
const status = ref<DocumentStatusResponse | null>(null)

const selectedFiles = reactive<Record<DocumentType, File | null>>({
  carte_etudiante: null,
  carte_identite: null,
  permis_conduire: null,
  carte_grise: null,
})

const documentLabels: Record<DocumentType, string> = {
  carte_etudiante: 'Carte étudiante',
  carte_identite: "Carte d'identité", 
  permis_conduire: 'Permis de conduire',
  carte_grise: 'Carte grise',
}

const statusLabels: Record<DocumentSoumis['status'], string> = {
  en_attente: 'En attente',
  approuve: 'Approuvé',
  rejete: 'Refusé',
}

const routeMessage = computed(() => {
  const message = route.query.message
  return typeof message === 'string' ? message : ''
})

const fallbackRequiredDocuments = computed<DocumentType[]>(() => {
  const role = authStore.membre?.role

  if (role === 'conducteur') {
    return ['permis_conduire', 'carte_grise']
  }

  if (authStore.membre?.account_type === 'professionnel') {
    return ['carte_identite']
  }

  return ['carte_etudiante']
})

const documents = computed(() => status.value?.documents ?? [])

const requiredDocuments = computed(() => status.value?.required_documents ?? fallbackRequiredDocuments.value)

const documentsByType = computed<Partial<Record<DocumentType, DocumentSoumis>>>(() => {
  const map: Partial<Record<DocumentType, DocumentSoumis>> = {}

  documents.value.forEach((doc) => {
    map[doc.type] = doc
  })

  return map
})

const canSubmit = computed(() => requiredDocuments.value.some((type) => Boolean(selectedFiles[type])))

const badgeClass = (docStatus: DocumentSoumis['status']) => {
  if (docStatus === 'approuve') return 'approved'
  if (docStatus === 'rejete') return 'rejected'
  return 'pending'
}

const formatDate = (value: string) => {
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

const onFileChange = (type: DocumentType, event: Event) => {
  const target = event.target as HTMLInputElement
  selectedFiles[type] = target.files?.[0] ?? null
}

const loadStatus = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await documentService.fetchPending()
    const payload = response.data

    if (Array.isArray(payload)) {
      status.value = {
        role: authStore.membre?.role ?? 'membre',
        account_type: authStore.membre?.account_type ?? null,
        has_verified_documents: Boolean(authStore.membre?.has_verified_documents),
        required_documents: fallbackRequiredDocuments.value,
        documents: payload,
      }
      return
    }

    status.value = payload
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Impossible de charger vos documents.'
  } finally {
    loading.value = false
  }
}

const submitDocuments = async () => {
  if (!requiredDocuments.value.length) {
    error.value = 'Aucun document n’est requis pour ce compte.'
    return
  }

  const payload = new FormData()

  requiredDocuments.value.forEach((type) => {
    const file = selectedFiles[type]
    if (file) {
      payload.append(type, file)
    }
  })

  if (![...payload.keys()].length) {
    error.value = 'Sélectionnez au moins un fichier avant de continuer.'
    return
  }

  submitting.value = true
  error.value = null
  successMessage.value = ''

  try {
    const response = await documentService.submit(payload)
    status.value = response.data
    requiredDocuments.value.forEach((type) => {
      selectedFiles[type] = null
    })
    successMessage.value = 'Documents envoyés. Ils sont maintenant en attente de vérification.'
    await loadStatus()
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'L’envoi a échoué.'
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  authStore.initFromStorage()
  await loadStatus()
})
</script>

<style scoped>
.pending-documents-page {
  max-width: 1120px;
  margin: 0 auto;
  padding: 40px 24px 72px;
  display: grid;
  gap: 20px;
}

.hero-card,
.panel,
.summary-card,
.state-card,
.info-banner {
  background: rgba(253, 249, 240, 0.08);
  border: 1px solid rgba(255, 225, 128, 0.14);
  backdrop-filter: blur(18px) saturate(170%);
  -webkit-backdrop-filter: blur(18px) saturate(170%);
  border-radius: 24px;
}

.hero-card {
  padding: 28px;
  display: flex;
  justify-content: space-between;
  gap: 24px;
  align-items: center;
}

.eyebrow {
  display: inline-flex;
  margin-bottom: 10px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 225, 128, 0.14);
  color: #ffe180;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0 0 10px;
  font-size: clamp(30px, 4vw, 48px);
  line-height: 1.05;
}

.hero-copy p,
.panel-head p,
.muted {
  margin: 0;
  color: rgba(253, 249, 240, 0.75);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  border-radius: 999px;
  background: rgba(255, 225, 128, 0.12);
  color: #ffe180;
  font-weight: 700;
  white-space: nowrap;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ffe180;
  box-shadow: 0 0 0 6px rgba(255, 225, 128, 0.12);
}

.info-banner,
.state-card {
  padding: 18px 20px;
}

.state-card.error {
  border-color: rgba(255, 105, 105, 0.3);
  color: #ffd2d2;
}

.summary-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.summary-card {
  padding: 18px 20px;
  display: grid;
  gap: 8px;
}

.summary-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(253, 249, 240, 0.58);
}

.panel {
  padding: 24px;
  display: grid;
  gap: 18px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: flex-start;
}

.panel-head h2,
.document-row h3 {
  margin: 0 0 6px;
}

.document-row,
.upload-card {
  padding: 18px 20px;
  border-radius: 20px;
  background: rgba(253, 249, 240, 0.05);
  border: 1px solid rgba(255, 225, 128, 0.1);
}

.document-row + .document-row {
  margin-top: 12px;
}

.row-top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.badge.pending {
  background: rgba(255, 225, 128, 0.12);
  color: #ffe180;
}

.badge.approved {
  background: rgba(69, 194, 131, 0.16);
  color: #7df0b7;
}

.badge.rejected {
  background: rgba(255, 105, 105, 0.14);
  color: #ffb6b6;
}

.rejection {
  margin-top: 10px;
  color: #ffd2d2;
}

.upload-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.upload-title {
  font-weight: 700;
}

.file-input {
  width: 100%;
  margin-top: 12px;
  color: rgba(253, 249, 240, 0.9);
}

.selected-file {
  margin: 10px 0 0;
  color: #ffe180;
  font-size: 14px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
}

.submit-btn {
  border: 0;
  border-radius: 999px;
  padding: 14px 20px;
  background: linear-gradient(135deg, #ffe180 0%, #9f9065 100%);
  color: #1b3d2f;
  font-weight: 800;
  cursor: pointer;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.empty-note,
.success-message {
  color: rgba(253, 249, 240, 0.78);
}

@media (max-width: 900px) {
  .hero-card,
  .panel-head {
    flex-direction: column;
  }

  .summary-grid,
  .upload-grid {
    grid-template-columns: 1fr;
  }
}
</style>
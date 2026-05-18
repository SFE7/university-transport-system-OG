<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useAvisStore } from '@/stores/avisStore'
import { useTrajetStore } from '@/stores/trajetStore'
import type { Avis } from '@/types'

const authStore = useAuthStore()
const avisStore = useAvisStore()
const trajetStore = useTrajetStore()

const avisMap = ref<Record<number, Avis[]>>({})
const actionMessage = ref('')
const actionError = ref('')
const createForms = ref<Record<number, { note: number; commentaire: string }>>({})
const editingId = ref<number | null>(null)
const editForm = reactive({ note: 5, commentaire: '' })

const normalizeAvisList = (value: unknown): Avis[] => {
  if (Array.isArray(value)) {
    return value.map((avis: any) => ({
      id: avis.id,
      conducteur_id: avis.conducteur_id,
      trajet_id: avis.trajet_id,
      note: avis.note ?? avis.rating ?? 0,
      commentaire: avis.commentaire ?? avis.comment ?? '',
      membre_id: avis.membre_id ?? avis.reviewer_id ?? avis.reviewer?.id ?? 0,
      created_at: avis.created_at,
    }))
  }

  if (value && typeof value === 'object' && Array.isArray((value as { data?: unknown }).data)) {
    return ((value as { data: any[] }).data ?? []).map((avis: any) => ({
      id: avis.id,
      conducteur_id: avis.conducteur_id,
      trajet_id: avis.trajet_id,
      note: avis.note ?? avis.rating ?? 0,
      commentaire: avis.commentaire ?? avis.comment ?? '',
      membre_id: avis.membre_id ?? avis.reviewer_id ?? avis.reviewer?.id ?? 0,
      created_at: avis.created_at,
    }))
  }

  return []
}

const getCreateForm = (trajetId: number) => {
  if (!createForms.value[trajetId]) {
    createForms.value[trajetId] = { note: 5, commentaire: '' }
  }

  return createForms.value[trajetId]
}

const history = computed(() => trajetStore.history)

const reloadAvisForConducteur = async (conducteurId: number) => {
  if (!conducteurId) return

  await avisStore.fetchByConducteur(conducteurId)
  avisStore.avis = normalizeAvisList(avisStore.avis) as any
  avisMap.value[conducteurId] = normalizeAvisList(avisStore.avis)
}

const avisForTrajet = (trajet: { id: number; conducteur?: { id: number } | null }) => {
  const conducteurId = trajet.conducteur?.id ?? -1
  return (avisMap.value[conducteurId] ?? []).filter(
    (avis) => avis.trajet_id === trajet.id && avis.membre_id === authStore.membre?.id,
  )
}

const submitCreate = async (trajet: { id: number; conducteur?: { id: number } | null }) => {
  const conducteurId = trajet.conducteur?.id
  if (!conducteurId) return

  actionMessage.value = ''
  actionError.value = ''

  avisStore.avis = normalizeAvisList(avisStore.avis) as any

  try {
    // send both shapes to be resilient to backend differences
    await avisStore.create({
      conducteur_id: conducteurId,
      trajet_id: trajet.id,
      rating: getCreateForm(trajet.id).note,
      comment: getCreateForm(trajet.id).commentaire,
      note: getCreateForm(trajet.id).note,
      commentaire: getCreateForm(trajet.id).commentaire,
    } as any)

    await reloadAvisForConducteur(conducteurId)
    createForms.value[trajet.id] = { note: 5, commentaire: '' }
    actionMessage.value = 'Avis publié.'
  } catch (err: any) {
    actionError.value = avisStore.error || err?.response?.data?.message || 'Erreur lors de la publication'
  }
}

const startEdit = (avis: Avis) => {
  editingId.value = avis.id
  editForm.note = avis.note
  editForm.commentaire = avis.commentaire
}

const submitEdit = async (avisId: number, conducteurId: number, trajetId?: number) => {
  actionMessage.value = ''
  actionError.value = ''
  try {
    await avisStore.update(avisId, {
      conducteur_id: conducteurId,
      trajet_id: trajetId,
      rating: editForm.note,
      comment: editForm.commentaire,
      note: editForm.note,
      commentaire: editForm.commentaire,
    } as any)

    await reloadAvisForConducteur(conducteurId)
    editingId.value = null
    actionMessage.value = 'Avis mis à jour.'
  } catch (err: any) {
    actionError.value = avisStore.error || err?.response?.data?.message || 'Erreur lors de la mise à jour'
  }
}

const removeAvis = async (avisId: number, conducteurId: number) => {
  if (!window.confirm('Confirmer suppression de cet avis ?')) return

  await avisStore.remove(avisId)
  await reloadAvisForConducteur(conducteurId)
  if (editingId.value === avisId) {
    editingId.value = null
  }
}

onMounted(async () => {
  await trajetStore.fetchHistory()

  const completedTrajets = history.value.filter((trajet) => trajet.status === 'completed' && trajet.conducteur?.id)
  const uniqueConducteurs = [...new Set(completedTrajets.map((trajet) => trajet.conducteur!.id))]

  for (const conducteurId of uniqueConducteurs) {
    await avisStore.fetchByConducteur(conducteurId)
    avisStore.avis = normalizeAvisList(avisStore.avis) as any
    avisMap.value[conducteurId] = normalizeAvisList(avisStore.avis)
  }
})

const getStatusLabel = (status: string) => {
  if (status === 'completed') return 'Terminé'
  if (status === 'cancelled') return 'Annulé'
  return status
}

const getStatusClass = (status: string) => {
  if (status === 'completed') return 'completed'
  if (status === 'cancelled') return 'cancelled'
  return 'pending'
}
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Historique des trajets</h1>
      <p class="subtitle">Vos trajets termines et annules.</p>
    </header>

    <div v-if="trajetStore.isLoading" class="status">Chargement...</div>
    <div v-if="trajetStore.error" class="status">{{ trajetStore.error }}</div>

    <div v-if="history.length" class="grid two">
      <div v-for="trajet in history" :key="trajet.id" class="glass history-card">
        <div class="tag-list">
          <span
            class="status-pill"
            :class="getStatusClass(trajet.status)"
          >
            {{ getStatusLabel(trajet.status) }}
          </span>
          <span class="status-pill pending">{{ trajet.departure_time }}</span>
        </div>
        <p>{{ trajet.departure_point }} -> {{ trajet.arrival_point }}</p>
        <p v-if="trajet.conducteur" class="muted">Conducteur: {{ trajet.conducteur.name }}</p>
        <p class="muted">Places: {{ trajet.available_seats }}</p>

        <div v-if="authStore.role === 'membre' && trajet.status === 'completed' && trajet.conducteur" class="avis-section">
          <p v-if="actionMessage" class="success-message">{{ actionMessage }}</p>
          <p v-if="actionError" class="error-message">{{ actionError }}</p>
          <h4 class="avis-title">Mon avis</h4>
          <div v-for="avis in avisForTrajet(trajet)" :key="avis.id" class="avis-item avis-card">
            <div class="avis-top">
              <span class="stars">{{ '★'.repeat(avis.note) }}{{ '☆'.repeat(5 - avis.note) }}</span>
              <p class="muted avis-comment">{{ avis.commentaire }}</p>
            </div>
            <div class="avis-actions">
              <small class="avis-meta">Trajet #{{ avis.trajet_id }}</small>
              <div class="action-buttons">
                <button class="primary-btn sm" type="button" @click="startEdit(avis)">Modifier</button>
                <button class="danger-btn sm" type="button" @click="removeAvis(avis.id, trajet.conducteur.id)">Supprimer</button>
              </div>
            </div>

            <form v-if="editingId === avis.id" class="edit-form" @submit.prevent="submitEdit(avis.id, trajet.conducteur.id, trajet.id)">
              <div class="edit-fields">
                <label class="field-label">Note</label>
                <input v-model.number="editForm.note" type="number" min="1" max="5" class="field-input" />
                <label class="field-label">Commentaire</label>
                <textarea v-model="editForm.commentaire" rows="3" class="field-input"></textarea>
              </div>
              <div class="form-actions-row">
                <button class="primary-btn sm" type="submit">Enregistrer</button>
                <button class="ghost-btn sm" type="button" @click="editingId = null">Annuler</button>
              </div>
            </form>
          </div>
          <form v-if="avisForTrajet(trajet).length === 0" class="create-form" @submit.prevent="submitCreate(trajet)">
            <div class="create-fields">
              <label class="field-label">Note</label>
              <input v-model.number="getCreateForm(trajet.id).note" type="number" min="1" max="5" class="field-input" />
              <label class="field-label">Commentaire</label>
              <textarea v-model="getCreateForm(trajet.id).commentaire" rows="3" class="field-input"></textarea>
            </div>
            <div class="form-actions-row">
              <button class="primary-btn sm" type="submit" :disabled="avisStore.isLoading">Publier un avis</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">Aucun trajet dans l'historique.</div>
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

.history-card {
  padding: 20px 24px;
  border-radius: 16px;
  transition: border-color 0.2s ease;
}

.avis-section {
  margin-top: 16px;
  border-top: 1px solid rgba(253, 249, 240, 0.1);
  padding-top: 12px;
  display: grid;
  gap: 10px;
}

.avis-title {
  font-size: 13px;
  font-weight: 700;
  color: rgba(253, 249, 240, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin: 0;
}

.avis-item {
  display: block;
}

/* Card style for avis */
.avis-card {
  margin-top: 10px;
  padding: 12px;
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.04);
}

.avis-top {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.stars {
  color: #ffd166;
  font-size: 18px;
  font-weight: 700;
  min-width: 90px;
}

.avis-comment {
  margin: 0;
  color: #f1f1f1;
}

.avis-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.field-input {
  width: 100%;
  border-radius: 10px;
  padding: 8px 10px;
  background: rgba(253,249,240,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  color: #fdf9f0;
}

.form-actions-row {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.create-form .create-fields,
.edit-form .edit-fields {
  display: grid;
  gap: 8px;
}

.primary-btn.sm,
.danger-btn.sm,
.ghost-btn.sm {
  padding: 8px 14px;
  font-size: 13px;
  border-radius: 8px;
  min-width: 88px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: background 0.15s ease, transform 0.08s ease, border-color 0.12s;
}

.primary-btn.sm {
  background: #2eb872;
  color: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.primary-btn.sm:hover { transform: translateY(-1px); background: #28a165; }

.danger-btn.sm {
  background: #ff6b6b;
  color: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.danger-btn.sm:hover { background: #ff5252; transform: translateY(-1px); }

.ghost-btn.sm {
  background: transparent;
  color: #fdf9f0;
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.ghost-btn.sm:hover { background: rgba(255, 255, 255, 0.03); }

.form-actions-row .primary-btn.sm,
.form-actions-row .ghost-btn.sm {
  min-width: 110px;
}

.history-card:hover {
  border-color: rgba(255, 225, 128, 0.5);
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

.status-pill.completed {
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

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}
</style>

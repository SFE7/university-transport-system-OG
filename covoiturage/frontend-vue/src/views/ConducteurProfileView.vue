<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAvisStore } from '@/stores/avisStore'
import { useAuthStore } from '@/stores/authStore'
import { useReservationStore } from '@/stores/reservationStore'
import type { Avis } from '@/types'

const route = useRoute()
const avisStore = useAvisStore()
const authStore = useAuthStore()

const conducteurId = computed(() => Number(route.params.id))
const isMembre = computed(() => authStore.role === 'membre')

const leaveForm = reactive({
  trajet_id: 0,
  note: 5,
  commentaire: '',
})

const editForm = reactive({
  note: 5,
  commentaire: '',
})

const editingAvisId = ref<number | null>(null)
const createError = ref('')
const editError = ref('')

const starsFor = (note: number) => '★'.repeat(note) + '☆'.repeat(5 - note)

const resetLeaveForm = () => {
  leaveForm.trajet_id = 0
  leaveForm.note = 5
  leaveForm.commentaire = ''
}

const startEdit = (avis: Avis) => {
  editingAvisId.value = avis.id
  editForm.note = avis.note
  editForm.commentaire = avis.commentaire
  editError.value = ''
}

const cancelEdit = () => {
  editingAvisId.value = null
  editForm.note = 5
  editForm.commentaire = ''
  editError.value = ''
}

const submitLeave = async () => {
  createError.value = ''
  try {
    await avisStore.create({
      conducteur_id: conducteurId.value,
      trajet_id: leaveForm.trajet_id,
      note: leaveForm.note,
      commentaire: leaveForm.commentaire,
    })
    resetLeaveForm()
  } catch {
    createError.value = avisStore.error || 'Failed to create avis'
  }
}

const submitEdit = async (avisId: number) => {
  editError.value = ''
  try {
    await avisStore.update(avisId, {
      note: editForm.note,
      commentaire: editForm.commentaire,
    })
    cancelEdit()
  } catch {
    editError.value = avisStore.error || 'Failed to update avis'
  }
}

const removeAvis = async (avisId: number) => {
  if (!window.confirm('Confirmer suppression de cet avis ?')) return
  try {
    await avisStore.remove(avisId)
    if (editingAvisId.value === avisId) {
      cancelEdit()
    }
  } catch {
    editError.value = avisStore.error || 'Failed to delete avis'
  }
}

onMounted(() => {
  avisStore.fetchByConducteur(conducteurId.value)
})
</script>

<template>
  <section>
    <header class="glass-card">
      <p class="muted">Profil public du conducteur #{{ conducteurId }}</p>
      <h1>Avis</h1>
      <p class="muted">{{ avisStore.avis.length }} avis publiés</p>
    </header>

    <p v-if="avisStore.isLoading" class="muted">Chargement...</p>
    <p v-if="avisStore.error" class="error-message">{{ avisStore.error }}</p>

    <section v-if="isMembre" class="glass-card">
      <h2>Laisser un avis</h2>
      <form @submit.prevent="submitLeave">
        <div>
          <label class="field-label" for="trajet-id">ID du trajet</label>
          <input id="trajet-id" v-model.number="leaveForm.trajet_id" type="number" min="1" class="field-input" />
        </div>
        <div>
          <label class="field-label" for="note">Note</label>
          <input id="note" v-model.number="leaveForm.note" type="number" min="1" max="5" class="field-input" />
        </div>
        <div>
          <label class="field-label" for="commentaire">Commentaire</label>
          <textarea id="commentaire" v-model="leaveForm.commentaire" rows="4" class="field-input"></textarea>
        </div>
        <button class="primary-btn" type="submit" :disabled="avisStore.isLoading">Publier</button>
        <p v-if="createError" class="error-message">{{ createError }}</p>
      </form>
    </section>

    <section class="glass-card">
      <h2>Avis reçus</h2>
      <p v-if="!avisStore.avis.length && !avisStore.isLoading" class="muted">Aucun avis pour le moment.</p>

      <div v-else>
        <article v-for="avis in avisStore.avis" :key="avis.id" class="glass-card">
          <p class="muted">Membre #{{ avis.membre_id }} · Trajet #{{ avis.trajet_id }}</p>
          <p><span>{{ starsFor(avis.note) }}</span></p>
          <p>{{ avis.commentaire }}</p>

          <div v-if="isMembre && avis.membre_id === authStore.membre?.id">
            <button type="button" class="primary-btn" @click="startEdit(avis)">Modifier</button>
            <button type="button" class="danger-btn" @click="removeAvis(avis.id)">Supprimer</button>

            <form v-if="editingAvisId === avis.id" @submit.prevent="submitEdit(avis.id)">
              <div>
                <label class="field-label" :for="`edit-note-${avis.id}`">Note</label>
                <input
                  :id="`edit-note-${avis.id}`"
                  v-model.number="editForm.note"
                  type="number"
                  min="1"
                  max="5"
                  class="field-input"
                />
              </div>
              <div>
                <label class="field-label" :for="`edit-commentaire-${avis.id}`">Commentaire</label>
                <textarea
                  :id="`edit-commentaire-${avis.id}`"
                  v-model="editForm.commentaire"
                  rows="4"
                  class="field-input"
                ></textarea>
              </div>
              <button class="primary-btn" type="submit">Enregistrer</button>
              <button class="ghost-btn" type="button" @click="cancelEdit">Annuler</button>
              <p v-if="editError" class="error-message">{{ editError }}</p>
            </form>
          </div>
        </article>
      </div>
    </section>
  </section>
</template>

<style scoped>
@media (max-width: 640px) {
  .row-actions {
    flex-direction: column;
  }
}
</style>

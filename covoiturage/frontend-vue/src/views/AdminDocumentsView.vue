<template>
  <section class="admin-page">
    <header class="page-header">
      <h1 class="page-title">Documents en attente</h1>
      <p class="page-subtitle">Validez ou refusez les justificatifs soumis par les membres.</p>
    </header>

    <div v-if="store.isLoading" class="empty-state">Chargement des documents...</div>
    <div v-else-if="store.error" class="empty-state error-state">{{ store.error }}</div>
    <div v-else-if="store.pending.length === 0" class="empty-state">Aucun document en attente.</div>

    <div v-else class="documents-container">
      <div class="panel documents-list">
        <article 
          v-for="doc in store.pending" 
          :key="doc.id" 
          class="row document-row"
          :class="{ active: selectedDoc?.id === doc.id }"
          @click="setSelectedDoc(doc)"
        >
          <div class="document-meta">
            <p class="document-title">Document #{{ doc.id }}</p>
            <h4>{{ formatDocumentType(doc.type) }}</h4>
            <p class="muted">Utilisateur: {{ doc.membre?.name || '—' }}</p>
          </div>
          <div class="row-actions">
            <button type="button" class="approve-btn" @click.stop="approve(doc.id)">Approuver</button>
            <button type="button" class="danger-btn" @click.stop="reject(doc.id)">Rejeter</button>
          </div>
        </article>
      </div>

      <div v-if="selectedDoc" class="panel preview-panel">
        <div class="preview-header">
          <div>
            <h3>{{ formatDocumentType(selectedDoc.type) }}</h3>
            <p class="preview-subtitle">Document #{{ selectedDoc.id }}</p>
          </div>
          <button type="button" class="close-btn" @click="closePreview">✕</button>
        </div>
        <div class="preview-content">
          <img 
            :src="documentImageUrl(selectedDoc)" 
            :alt="`${formatDocumentType(selectedDoc.type)} - ${selectedDoc.membre?.name}`"
            class="preview-image"
            style="max-width:100%; max-height:400px; width:100%; object-fit:contain; display:block;"
          />
          <div class="preview-details">
            <p><strong>Type:</strong> {{ formatDocumentType(selectedDoc.type) }}</p>
            <p><strong>Utilisateur:</strong> {{ selectedDoc.membre?.name || '—' }}</p>
            <p><strong>Status:</strong> {{ selectedDoc.status }}</p>
          </div>
          <div class="preview-actions">
            <button type="button" class="approve-btn" @click="approve(selectedDoc.id)">Approuver</button>
            <button type="button" class="danger-btn" @click="reject(selectedDoc.id)">Rejeter</button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useDocumentStore } from '@/stores/documentStore'
import type { DocumentSoumis } from '@/types/admin'

const store = useDocumentStore()
const selectedDoc = ref<DocumentSoumis | null>(null)

onMounted(async () => {
  await store.fetchPending()
})

const formatDocumentType = (type: DocumentSoumis['type']) => {
  const labels: Record<DocumentSoumis['type'], string> = {
    carte_etudiante: 'Carte étudiante',
    carte_identite: "Carte d'identité",
    permis_conduire: 'Permis de conduire',
    carte_grise: 'Carte grise',
  }

  return labels[type] ?? type
}

const documentImageUrl = (doc: DocumentSoumis | null) => {
  if (!doc) return ''

  if (doc.file_path) {
    if (/^https?:\/\//i.test(doc.file_path)) {
      return doc.file_path
    }

    const baseUrl = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
    return `${baseUrl}/storage/${doc.file_path.replace(/^\/+/, '')}`
  }

  return doc.url || ''
}

const setSelectedDoc = (doc: DocumentSoumis) => {
  selectedDoc.value = doc
}

const closePreview = () => {
  selectedDoc.value = null
}

const approve = async (id: number) => {
  await store.approve(id)
  selectedDoc.value = null
}

const reject = async (id: number) => {
  await store.reject(id, 'rejected by admin')
  selectedDoc.value = null
}
</script>

<style scoped>
.admin-page {
  padding: 40px 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px;
}

.page-subtitle {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0;
}

.panel,
.row {
  background: rgba(253, 249, 240, 0.06);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.15);
  border-radius: 20px;
}

.panel {
  padding: 24px;
  margin-bottom: 20px;
}

.documents-list {
  margin-bottom: 0;
}

.document-row {
  padding: 26px 28px;
  margin-bottom: 0;
  border-radius: 22px;
  min-height: 166px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}

.document-row:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 225, 128, 0.4);
  background: rgba(253, 249, 240, 0.1);
}

.document-row:active {
  transform: scale(0.98);
}

.document-row.active {
  border-color: rgba(255, 225, 128, 0.5);
  background: rgba(255, 225, 128, 0.12);
}

.document-row + .document-row {
  margin-top: 18px;
}

.document-meta {
  display: grid;
  gap: 16px;
}

.document-title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #fdf9f0;
}

.document-row h4 {
  margin: 0;
  font-size: 19px;
  color: #fdf9f0;
  text-transform: lowercase;
}

.row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  transition: border-color 0.2s ease, transform 0.2s ease, background 0.2s ease;
}

.approve-btn,
.danger-btn {
  border-radius: 999px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.approve-btn {
  background: rgba(100, 200, 120, 0.15);
  border: 1px solid rgba(100, 200, 120, 0.3);
  color: #6ec47a;
}

.approve-btn:hover {
  background: rgba(100, 200, 120, 0.3);
}

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 15px;
}

.empty-state.error-state {
  color: #ffb3b3;
  padding: 40px 24px;
  background: rgba(255, 107, 107, 0.1);
  border: 1px solid rgba(255, 107, 107, 0.2);
  border-radius: 20px;
}

.documents-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  align-items: start;
}

.documents-list {
  grid-column: 1;
}

.preview-panel {
  grid-column: 2;
  position: sticky;
  top: 100px;
  display: flex;
  flex-direction: column;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.preview-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #fdf9f0;
}

.preview-subtitle {
  margin: 6px 0 0;
  color: rgba(253, 249, 240, 0.65);
  font-size: 13px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: rgba(253, 249, 240, 0.6);
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.close-btn:hover {
  color: rgba(253, 249, 240, 0.9);
}

.preview-content {
  flex: 1;
  display: grid;
  gap: 16px;
  align-items: start;
  min-height: 300px;
  overflow: auto;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  border-radius: 16px;
  object-fit: contain;
}

.preview-details {
  display: grid;
  gap: 8px;
  color: #fdf9f0;
}

.preview-details p {
  margin: 0;
}

.preview-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

@media (max-width: 720px) {
  .row {
    flex-direction: column;
    align-items: flex-start;
  }

  .row-actions {
    justify-content: flex-start;
  }

  .documents-container {
    grid-template-columns: 1fr;
  }

  .documents-list {
    grid-column: 1;
  }

  .preview-panel {
    grid-column: 1;
    position: static;
  }
}
</style>

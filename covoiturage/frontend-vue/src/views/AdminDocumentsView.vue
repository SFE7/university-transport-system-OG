<template>
  <section class="admin-page">
    <header class="page-header">
      <h1 class="page-title">Documents en attente</h1>
      <p class="page-subtitle">Validez ou refusez les justificatifs soumis par les membres.</p>
    </header>

    <div v-if="pending.length === 0" class="empty-state">Aucun document en attente.</div>

    <div v-else class="panel">
      <article v-for="doc in pending" :key="doc.id" class="row">
        <div>
          <h4>{{ doc.type }}</h4>
          <p class="muted">{{ doc.membre?.name || '—' }}</p>
          <a class="primary-link" :href="doc.file_path" target="_blank">Voir</a>
        </div>
        <div class="row-actions">
          <button type="button" class="approve-btn" @click="approve(doc.id)">Approuver</button>
          <button type="button" class="danger-btn" @click="reject(doc.id)">Rejeter</button>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useDocumentStore } from '@/stores/documentStore'

const store = useDocumentStore()

onMounted(() => store.fetchPending())

const pending = store.pending
const approve = async (id: number) => await store.approve(id)
const reject = async (id: number) => await store.reject(id, 'rejected by admin')
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

.row {
  padding: 20px 24px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
}

.row:hover {
  border-color: rgba(255, 225, 128, 0.5);
}

.muted {
  color: rgba(253, 249, 240, 0.6);
}

.primary-link {
  color: #9f9065;
  text-decoration: none;
}

.primary-link:hover {
  color: #ffe180;
}

.row-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
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

@media (max-width: 720px) {
  .row {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<template>
  <section class="admin-page">
    <header class="page-header">
      <h1 class="page-title">Statistiques</h1>
      <p class="page-subtitle">Vue d'ensemble des indicateurs clés de la plateforme.</p>
    </header>

    <div v-if="!stats" class="empty-state">Chargement...</div>
    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value">{{ stats.membres?.total ?? 0 }}</div>
        <div class="stat-label">Utilisateurs</div>
        <div class="stat-sub">Total des comptes</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🚌</div>
        <div class="stat-value">{{ stats.bus?.chauffeurs ?? 0 }}</div>
        <div class="stat-label">Conducteurs</div>
        <div class="stat-sub">Bus et conducteurs actifs</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🛣️</div>
        <div class="stat-value">{{ stats.trajets?.total ?? 0 }}</div>
        <div class="stat-label">Trajets</div>
        <div class="stat-sub">Trajets publiés</div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useStatistiquesStore } from '@/stores/statistiquesStore'

const store = useStatistiquesStore()
onMounted(() => store.fetchStats())

const stats = store.stats
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.stat-card {
  background: rgba(253, 249, 240, 0.06);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.15);
  border-radius: 20px;
  padding: 24px;
  text-align: center;
}

.stat-icon {
  font-size: 24px;
  margin-bottom: 10px;
}

.stat-value {
  color: #ffe180;
  font-size: 36px;
  font-weight: 700;
}

.stat-label {
  color: rgba(253, 249, 240, 0.6);
  font-size: 13px;
  margin-top: 4px;
}

.stat-sub {
  color: rgba(253, 249, 240, 0.4);
  font-size: 12px;
  margin-top: 8px;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 15px;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>

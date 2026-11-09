<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notificationStore'

const notificationStore = useNotificationStore()

onMounted(() => {
  notificationStore.fetchMyNotifications()
})

const notifications = computed(() => notificationStore.notifications)
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Notifications</h1>
      <p class="subtitle">Suivez les mises a jour importantes.</p>
    </header>

    <div v-if="notificationStore.isLoading" class="status">Chargement...</div>
    <div v-if="notificationStore.error" class="status">{{ notificationStore.error }}</div>

    <div v-if="notifications.length" class="grid two">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="glass notification-card"
        :class="notification.is_read ? 'read' : 'unread'"
      >
        <span class="unread-dot" v-if="!notification.is_read"></span>
        <div class="tag-list">
          <span class="badge">{{ notification.type }}</span>
          <span class="badge gray">{{ notification.created_at }}</span>
        </div>
        <p>{{ notification.message }}</p>
        <button
          v-if="!notification.is_read"
          class="primary-btn"
          type="button"
          @click="notificationStore.markAsRead(notification.id)"
        >
          Marquer comme lu
        </button>
        <button
          class="danger-btn"
          type="button"
          @click="notificationStore.remove(notification.id)"
        >
          Supprimer
        </button>
      </div>
    </div>
    <div v-else class="empty-state">Aucune notification pour le moment.</div>
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

.notification-card {
  position: relative;
  padding: 20px 24px;
  border-radius: 16px;
  transition: border-color 0.2s ease;
}

.notification-card:hover {
  border-color: rgba(255, 225, 128, 0.5);
}

.notification-card.unread {
  border-left: 3px solid #ffe180;
}

.notification-card.read {
  border-left: 3px solid rgba(253, 249, 240, 0.1);
}

.unread-dot {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #ffe180;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border: 1px solid rgba(255, 225, 128, 0.3);
}

.badge.gray {
  background: rgba(253, 249, 240, 0.1);
  color: rgba(253, 249, 240, 0.7);
  border-color: rgba(253, 249, 240, 0.12);
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

.danger-btn {
  margin-left: 12px;
  background: transparent;
  color: #f0b7b1;
  border: 1px solid rgba(240, 183, 177, 0.35);
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.danger-btn:hover {
  background: rgba(240, 183, 177, 0.12);
  color: #fdf9f0;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 16px;
}
</style>

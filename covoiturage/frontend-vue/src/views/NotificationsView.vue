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
  <section class="page">
    <header>
      <h1 class="page-title">Notifications</h1>
      <p class="subtitle">Suivez les mises a jour importantes.</p>
    </header>

    <div v-if="notificationStore.isLoading" class="status">Chargement...</div>
    <div v-if="notificationStore.error" class="status">{{ notificationStore.error }}</div>

    <div class="grid two">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="card"
        :class="{ 'unread': !notification.is_read }"
      >
        <div class="tag-list">
          <span class="badge">{{ notification.type }}</span>
          <span class="badge gray">{{ notification.created_at }}</span>
        </div>
        <p>{{ notification.message }}</p>
        <button
          v-if="!notification.is_read"
          class="btn ghost"
          type="button"
          @click="notificationStore.markAsRead(notification.id)"
        >
          Marquer comme lu
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.unread {
  border-color: rgba(216, 107, 58, 0.5);
}
</style>

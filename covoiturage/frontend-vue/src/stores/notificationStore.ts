import { ref } from 'vue'
import { defineStore } from 'pinia'
import notificationService from '@/services/notificationService'
import type { Notification } from '@/types'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchMyNotifications = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await notificationService.getMyNotifications()
      notifications.value = response.data
      unreadCount.value = response.data.filter((item) => !item.is_read).length
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load notifications'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const markAsRead = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const wasUnread = notifications.value.find((item) => item.id === id)?.is_read === false
      const response = await notificationService.markAsRead(id)
      notifications.value = notifications.value.map((item) =>
        item.id === id ? response.data : item
      )
      if (wasUnread && response.data.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      } else {
        unreadCount.value = notifications.value.filter((item) => !item.is_read).length
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to update notification'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    error,
    fetchMyNotifications,
    markAsRead,
  }
})

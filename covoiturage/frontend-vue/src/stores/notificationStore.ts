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
      const items = response.data.data ?? []
      notifications.value = items
      unreadCount.value = items.filter((item) => !item.is_read).length
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
    const previousState = notifications.value.map((item) => ({ ...item }))
    try {
      const wasUnread = notifications.value.find((item) => item.id === id)?.is_read === false
      notifications.value = notifications.value.map((item) =>
        item.id === id ? { ...item, is_read: true } : item
      )
      const response = await notificationService.markAsRead(id)
      const updatedNotification = response.data.data
      notifications.value = notifications.value.map((item) =>
        item.id === id ? updatedNotification : item
      )
      if (wasUnread && updatedNotification.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      } else {
        unreadCount.value = notifications.value.filter((item) => !item.is_read).length
      }
      return response
    } catch (err: any) {
      notifications.value = previousState
      unreadCount.value = previousState.filter((item) => !item.is_read).length
      error.value = err?.response?.data?.message || 'Failed to update notification'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const remove = async (id: number) => {
    isLoading.value = true
    error.value = null
    const previousState = notifications.value.map((item) => ({ ...item }))
    try {
      const wasUnread = notifications.value.find((item) => item.id === id)?.is_read === false
      notifications.value = notifications.value.filter((item) => item.id !== id)
      if (wasUnread) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }

      const response = await notificationService.removeNotification(id)
      return response
    } catch (err: any) {
      notifications.value = previousState
      unreadCount.value = previousState.filter((item) => !item.is_read).length
      error.value = err?.response?.data?.message || 'Failed to delete notification'
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
    remove,
  }
})

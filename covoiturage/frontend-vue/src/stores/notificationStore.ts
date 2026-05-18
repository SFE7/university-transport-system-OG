import { ref } from 'vue'
import { defineStore } from 'pinia'
import notificationService from '@/services/notificationService'
import type { Notification } from '@/types'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const extractNotificationArray = (payload: unknown): Notification[] => {
    if (Array.isArray(payload)) {
      return payload as Notification[]
    }

    if (payload && typeof payload === 'object') {
      const value = payload as { data?: unknown; notifications?: unknown }

      if (Array.isArray(value.data)) {
        return value.data as Notification[]
      }

      if (Array.isArray(value.notifications)) {
        return value.notifications as Notification[]
      }
    }

    return []
  }

  const extractNotificationItem = (payload: unknown): Notification | null => {
    if (payload && typeof payload === 'object') {
      const value = payload as { data?: unknown }

      if (value.data && typeof value.data === 'object' && !Array.isArray(value.data)) {
        return value.data as Notification
      }

      return payload as Notification
    }

    return null
  }

  const fetchMyNotifications = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await notificationService.getMyNotifications()
      const extractedNotifications = extractNotificationArray(response.data)
      notifications.value = extractedNotifications
      unreadCount.value = extractedNotifications.filter((item) => !item.is_read).length
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
      const updatedNotification = extractNotificationItem(response.data)
      notifications.value = notifications.value.map((item) =>
        item.id === id && updatedNotification ? updatedNotification : item
      )
      if (wasUnread && updatedNotification?.is_read) {
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

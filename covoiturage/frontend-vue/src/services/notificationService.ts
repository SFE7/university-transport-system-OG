import apiClient from '@/lib/apiClient'
import type { ApiResponse, Notification, PaginatedResponse } from '@/types'

const getMyNotifications = async (): Promise<PaginatedResponse<Notification>> => {
  const response = await apiClient.get<PaginatedResponse<Notification>>('/notifications')
  return response.data
}

const markAsRead = async (id: number): Promise<ApiResponse<Notification>> => {
  const response = await apiClient.patch<ApiResponse<Notification>>(`/notifications/${id}/read`)
  return response.data
}

const removeNotification = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/notifications/${id}`)
  return response.data
}

const broadcastNotification = async (message: string, type: string, target_roles: string[]) => {
  const response = await apiClient.post('/admin/notifications/broadcast', { message, type, target_roles })
  return response.data
}

export default {
  getMyNotifications,
  markAsRead,
  removeNotification,
  broadcastNotification,
}

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

export default {
  getMyNotifications,
  markAsRead,
}

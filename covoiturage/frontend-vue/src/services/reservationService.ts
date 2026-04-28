import apiClient from '@/lib/apiClient'
import type { ApiResponse, PaginatedResponse, Reservation } from '@/types'

const getMyReservations = async (): Promise<PaginatedResponse<Reservation>> => {
  const response = await apiClient.get<PaginatedResponse<Reservation>>('/reservations')
  return response.data
}

const create = async (payload: { trajet_id: number }): Promise<ApiResponse<Reservation>> => {
  const response = await apiClient.post<ApiResponse<Reservation>>('/reservations', payload)
  return response.data
}

const getOne = async (id: number): Promise<ApiResponse<Reservation>> => {
  const response = await apiClient.get<ApiResponse<Reservation>>(`/reservations/${id}`)
  return response.data
}

const cancel = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/reservations/${id}`)
  return response.data
}

const accept = async (id: number): Promise<ApiResponse<Reservation>> => {
  const response = await apiClient.patch<ApiResponse<Reservation>>(`/reservations/${id}/accept`)
  return response.data
}

const refuse = async (id: number): Promise<ApiResponse<Reservation>> => {
  const response = await apiClient.patch<ApiResponse<Reservation>>(`/reservations/${id}/refuse`)
  return response.data
}

export default {
  getMyReservations,
  create,
  getOne,
  cancel,
  accept,
  refuse,
}

import apiClient from '@/lib/apiClient'
import type { ApiResponse, PaginatedResponse, Trajet } from '@/types'

const getAll = async (filters?: {
  departure_point?: string
  arrival_point?: string
  departure_time?: string
  available_seats?: number
}): Promise<PaginatedResponse<Trajet>> => {
  const response = await apiClient.get('/trajets', { params: filters })
  return response.data.data  // ← unwrap the Laravel envelope
}

const getOne = async (id: number): Promise<ApiResponse<Trajet>> => {
  const response = await apiClient.get<ApiResponse<Trajet>>(`/trajets/${id}`)
  return response.data
}

const create = async (payload: {
  departure_point: string
  arrival_point: string
  departure_time: string
  available_seats: number
}): Promise<ApiResponse<Trajet>> => {
  const response = await apiClient.post<ApiResponse<Trajet>>('/trajets', payload)
  return response.data
}

const update = async (id: number, payload: Partial<Trajet>): Promise<ApiResponse<Trajet>> => {
  const response = await apiClient.put<ApiResponse<Trajet>>(`/trajets/${id}`, payload)
  return response.data
}

const cancel = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/trajets/${id}`)
  return response.data
}

const getHistory = async (): Promise<PaginatedResponse<Trajet>> => {
  const response = await apiClient.get('/trajets/history')
  return response.data.data  // ← same fix
}

export default {
  getAll,
  getOne,
  create,
  update,
  cancel,
  getHistory,
}

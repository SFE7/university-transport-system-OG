import apiClient from '@/lib/apiClient'
import type { ApiResponse, PaginatedResponse, Trajet } from '@/types'

type CreateTrajetPayload = {
  departure_point: string
  arrival_point: string
  departure_time: string
  available_seats: number
  car_category: string
  car_model: string
  car_photo_url?: string | null
  carPhotoFile?: File | null
}

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

const create = async (payload: CreateTrajetPayload): Promise<ApiResponse<Trajet>> => {
  let requestBody: CreateTrajetPayload | FormData = {
    departure_point: payload.departure_point,
    arrival_point: payload.arrival_point,
    departure_time: payload.departure_time,
    available_seats: payload.available_seats,
    car_category: payload.car_category,
    car_model: payload.car_model,
  }

  // Only include `car_photo_url` when it is explicitly provided (not null/undefined)
  if (payload.car_photo_url != null) {
    ;(requestBody as any).car_photo_url = payload.car_photo_url
  }

  if (payload.carPhotoFile) {
    const formData = new FormData()
    formData.append('departure_point', payload.departure_point)
    formData.append('arrival_point', payload.arrival_point)
    formData.append('departure_time', payload.departure_time)
    formData.append('available_seats', String(payload.available_seats))
    formData.append('car_category', payload.car_category)
    formData.append('car_model', payload.car_model)
    if (payload.car_photo_url) {
      formData.append('car_photo_url', payload.car_photo_url)
    }
    formData.append('car_photo', payload.carPhotoFile)
    requestBody = formData
  }

  console.log('[trajetService.create] request body', requestBody)

  const response = await apiClient.post<ApiResponse<Trajet>>('/trajets', requestBody)
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
  return response.data
}

const getMine = async (): Promise<PaginatedResponse<Trajet>> => {
  const response = await apiClient.get('/trajets/mes-trajets')
  return response.data.data
}

export default {
  getAll,
  getOne,
  create,
  update,
  cancel,
  getHistory,
  getMine,
}

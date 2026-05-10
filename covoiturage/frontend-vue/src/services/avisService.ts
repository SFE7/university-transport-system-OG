import apiClient from '@/lib/apiClient'
import type { ApiResponse, Avis, PaginatedResponse } from '@/types'

const getByConducteur = async (membreId: number): Promise<PaginatedResponse<Avis>> => {
  const response = await apiClient.get<PaginatedResponse<Avis>>(`/membres/${membreId}/avis`)
  return response.data
}

const create = async (payload: {
  conducteur_id: number
  trajet_id: number
  rating: number
  comment?: string
}): Promise<ApiResponse<Avis>> => {
  const response = await apiClient.post<ApiResponse<Avis>>('/avis', payload)
  return response.data
}

const update = async (id: number, payload: {
  conducteur_id: number
  trajet_id: number
  rating: number
  comment?: string
}): Promise<ApiResponse<Avis>> => {
  const response = await apiClient.put<ApiResponse<Avis>>(`/avis/${id}`, payload)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/avis/${id}`)
  return response.data
}

export default {
  getByConducteur,
  create,
  update,
  delete: remove,
}

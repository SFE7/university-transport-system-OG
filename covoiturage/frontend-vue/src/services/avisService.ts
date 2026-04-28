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

export default {
  getByConducteur,
  create,
}

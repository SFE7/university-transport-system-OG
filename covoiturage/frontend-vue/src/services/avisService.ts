import apiClient from '@/lib/apiClient'
import type { ApiResponse, Avis } from '@/types'

export type CreateAvisPayload = {
  conducteur_id: number
  trajet_id: number
  note: number
  commentaire: string
}

export type UpdateAvisPayload = {
  note: number
  commentaire: string
}

const getByCondukteur = async (conducteurId: number): Promise<ApiResponse<Avis[]>> => {
  const response = await apiClient.get<ApiResponse<Avis[]>>(`/membres/${conducteurId}/avis`)
  return response.data
}

const create = async (payload: CreateAvisPayload): Promise<ApiResponse<Avis>> => {
  const response = await apiClient.post<ApiResponse<Avis>>('/avis', payload)
  return response.data
}

const update = async (id: number, payload: UpdateAvisPayload): Promise<ApiResponse<Avis>> => {
  const response = await apiClient.put<ApiResponse<Avis>>(`/avis/${id}`, payload)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/avis/${id}`)
  return response.data
}

export default {
  getByCondukteur,
  create,
  update,
  remove,
}

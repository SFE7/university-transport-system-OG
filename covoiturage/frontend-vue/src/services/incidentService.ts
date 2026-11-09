import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { IncidentBus } from '@/types/bus'

const getAll = async (): Promise<ApiResponse<IncidentBus[]>> => {
  const response = await apiClient.get<ApiResponse<IncidentBus[]>>('/incidents')
  return response.data
}

const create = async (payload: {
  ligne_bus_id: number
  type: string
  description: string
}): Promise<ApiResponse<IncidentBus>> => {
  const response = await apiClient.post<ApiResponse<IncidentBus>>('/incidents', payload)
  return response.data
}

const resolve = async (id: number): Promise<ApiResponse<IncidentBus>> => {
  const response = await apiClient.patch<ApiResponse<IncidentBus>>(`/incidents/${id}/resolve`)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/incidents/${id}`)
  return response.data
}

export default {
  getAll,
  create,
  resolve,
  delete: remove,
}

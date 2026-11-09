import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { HoraireBus } from '@/types/bus'

const create = async (payload: {
  ligne_bus_id: number
  chauffeur_id: number
  departure_time: string
  days: string[]
}): Promise<ApiResponse<HoraireBus>> => {
  const response = await apiClient.post<ApiResponse<HoraireBus>>('/horaires', payload)
  return response.data
}

const update = async (id: number, payload: Partial<HoraireBus>): Promise<ApiResponse<HoraireBus>> => {
  const response = await apiClient.put<ApiResponse<HoraireBus>>(`/horaires/${id}`, payload)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/horaires/${id}`)
  return response.data
}

export default {
  create,
  update,
  delete: remove,
}

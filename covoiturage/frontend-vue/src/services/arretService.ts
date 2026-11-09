import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { ArretBus } from '@/types/bus'

const create = async (payload: {
  name: string
  latitude: number
  longitude: number
  order: number
  ligne_bus_id: number
}): Promise<ApiResponse<ArretBus>> => {
  const response = await apiClient.post<ApiResponse<ArretBus>>('/arrets', payload)
  return response.data
}

const update = async (id: number, payload: Partial<ArretBus>): Promise<ApiResponse<ArretBus>> => {
  const response = await apiClient.put<ApiResponse<ArretBus>>(`/arrets/${id}`, payload)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/arrets/${id}`)
  return response.data
}

export default {
  create,
  update,
  delete: remove,
}

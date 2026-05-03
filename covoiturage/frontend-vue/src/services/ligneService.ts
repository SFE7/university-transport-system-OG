import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { HoraireBus, LigneBus } from '@/types/bus'

const getAll = async (): Promise<ApiResponse<LigneBus[]>> => {
  const response = await apiClient.get<ApiResponse<LigneBus[]>>('/lignes')
  return response.data
}

const getOne = async (id: number): Promise<ApiResponse<LigneBus>> => {
  const response = await apiClient.get<ApiResponse<LigneBus>>(`/lignes/${id}`)
  return response.data
}

const getSchedules = async (id: number, day?: string): Promise<ApiResponse<HoraireBus[]>> => {
  const response = await apiClient.get<ApiResponse<HoraireBus[]>>(`/lignes/${id}/schedules`, {
    params: day ? { day } : undefined,
  })
  return response.data
}

const create = async (payload: {
  name: string
  description?: string
}): Promise<ApiResponse<LigneBus>> => {
  const response = await apiClient.post<ApiResponse<LigneBus>>('/lignes', payload)
  return response.data
}

const update = async (id: number, payload: Partial<LigneBus>): Promise<ApiResponse<LigneBus>> => {
  const response = await apiClient.put<ApiResponse<LigneBus>>(`/lignes/${id}`, payload)
  return response.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/lignes/${id}`)
  return response.data
}

export default {
  getAll,
  getOne,
  getSchedules,
  create,
  update,
  delete: remove,
}

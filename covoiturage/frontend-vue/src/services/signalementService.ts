import apiClient from '@/lib/apiClient'
import type { ApiResponse, PaginatedResponse } from '@/types'
import type { Signalement } from '@/types/admin'

const create = async (payload: { reported_id: number; reason: string }) : Promise<ApiResponse<Signalement>> => {
  const res = await apiClient.post<ApiResponse<Signalement>>('/signalements', payload)
  return res.data
}

const fetchAll = async (params?: { status?: string }) : Promise<ApiResponse<Signalement[]>> => {
  const res = await apiClient.get<ApiResponse<Signalement[]>>('/admin/signalements', { params })
  return res.data
}

const updateStatus = async (id: number, status: string): Promise<ApiResponse<Signalement>> => {
  const res = await apiClient.patch<ApiResponse<Signalement>>(`/admin/signalements/${id}/statut`, { status })
  return res.data
}

const remove = async (id: number): Promise<ApiResponse<null>> => {
  const res = await apiClient.delete<ApiResponse<null>>(`/admin/signalements/${id}`)
  return res.data
}

export default { create, fetchAll, updateStatus, remove }

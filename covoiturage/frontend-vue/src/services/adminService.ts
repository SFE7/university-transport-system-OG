import apiClient from '@/lib/apiClient'
import type { ApiResponse, Membre, PaginatedResponse } from '@/types'

const getMembres = async (): Promise<PaginatedResponse<Membre>> => {
  const response = await apiClient.get<PaginatedResponse<Membre>>('/admin/membres')
  return response.data
}

const updateRole = async (id: number, role: string): Promise<ApiResponse<Membre>> => {
  const response = await apiClient.patch<ApiResponse<Membre>>(`/admin/membres/${id}/role`, { role })
  return response.data
}

const deleteMembre = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/admin/membres/${id}`)
  return response.data
}

export default {
  getMembres,
  updateRole,
  deleteMembre,
}

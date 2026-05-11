import apiClient from '@/lib/apiClient'
import type { ApiResponse, Membre, PaginatedResponse } from '@/types'

const getMembres = async (): Promise<PaginatedResponse<Membre>> => {
  const response = await apiClient.get('/admin/membres')
  console.log('getMembres raw response:', response)
  console.log('getMembres response.data:', response.data)
  // API returns: { data: { current_page, data: [...], ... }, message, status }
  const payload = response.data as ApiResponse<any>
  const paginated = payload.data || {}
  const result: PaginatedResponse<Membre> = {
    data: paginated.data || [],
    message: payload.message,
    status: payload.status,
    meta: {
      current_page: paginated.current_page,
      last_page: paginated.last_page,
      per_page: paginated.per_page,
      total: paginated.total,
    },
  }
  return result
}

const updateRole = async (id: number, role: string): Promise<ApiResponse<Membre>> => {
  const response = await apiClient.patch<ApiResponse<Membre>>(`/admin/membres/${id}/role`, { role })
  return response.data
}

const deleteMembre = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/admin/membres/${id}`)
  return response.data
}

const suspendMember = async (id: number, reason: string): Promise<ApiResponse<Membre>> => {
  const response = await apiClient.patch<ApiResponse<Membre>>(`/admin/membres/${id}/suspend`, { reason })
  return response.data
}

const banMember = async (id: number, reason: string): Promise<ApiResponse<Membre>> => {
  const response = await apiClient.patch<ApiResponse<Membre>>(`/admin/membres/${id}/bannir`, { reason })
  return response.data
}

export default {
  getMembres,
  updateRole,
  deleteMembre,
  suspendMember,
  banMember,
}

import apiClient from '@/lib/apiClient'
import type { ApiResponse, Membre, PaginatedResponse } from '@/types'
import type { HoraireBus } from '@/types/bus'

type HorairePayload = {
  ligne_bus_id: number
  chauffeur_id: number
  departure_time: string
  days: string[]
}

const getMembres = async (): Promise<PaginatedResponse<Membre>> => {
  const response = await apiClient.get('/admin/membres')
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

const getChauffeurs = async (): Promise<ApiResponse<Membre[]>> => {
  const response = await apiClient.get<ApiResponse<Membre[]>>('/chauffeurs')
  return response.data
}

const createChauffeur = async (payload: {
  name: string
  email: string
  password: string
  password_confirmation: string
}): Promise<ApiResponse<Membre>> => {
  const response = await apiClient.post<ApiResponse<Membre>>('/chauffeurs', payload)
  return response.data
}

const deleteChauffeur = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/chauffeurs/${id}`)
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

const getHoraires = async (): Promise<ApiResponse<HoraireBus[]>> => {
  const response = await apiClient.get<ApiResponse<HoraireBus[]>>('/horaires')
  return response.data
}

const createHoraire = async (payload: HorairePayload): Promise<ApiResponse<HoraireBus>> => {
  const response = await apiClient.post<ApiResponse<HoraireBus>>('/horaires', payload)
  return response.data
}

const updateHoraire = async (id: number, payload: HorairePayload): Promise<ApiResponse<HoraireBus>> => {
  const response = await apiClient.put<ApiResponse<HoraireBus>>(`/horaires/${id}`, payload)
  return response.data
}

const deleteHoraire = async (id: number): Promise<ApiResponse<null>> => {
  const response = await apiClient.delete<ApiResponse<null>>(`/horaires/${id}`)
  return response.data
}

export default {
  getMembres,
  updateRole,
  deleteMembre,
  getChauffeurs,
  createChauffeur,
  deleteChauffeur,
  suspendMember,
  banMember,
  getHoraires,
  createHoraire,
  updateHoraire,
  deleteHoraire,
}

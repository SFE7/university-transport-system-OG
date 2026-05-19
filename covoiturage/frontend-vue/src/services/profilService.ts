import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { Vehicule } from '@/types/admin'
import type { Membre } from '@/types'

const fetchProfile = async (id: number): Promise<ApiResponse<Membre>> => {
  const res = await apiClient.get<ApiResponse<Membre>>(`/membres/${id}/profil`)
  return res.data
}

const updateProfile = async (payload: Partial<Membre>): Promise<ApiResponse<Membre>> => {
  const res = await apiClient.put<ApiResponse<Membre>>('/membres/profil', payload)
  return res.data
}

const updateVehicule = async (payload: FormData | { marque: string; modele: string; immatriculation: string; couleur?: string }) : Promise<ApiResponse<Vehicule>> => {
  // If payload is FormData, send multipart with method spoofing via POST
  if (payload instanceof FormData) {
    const res = await apiClient.post<FormData, any>('/conducteurs/vehicule', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return res.data
  }

  const res = await apiClient.put<ApiResponse<Vehicule>>('/conducteurs/vehicule', payload)
  return res.data
}

export default { fetchProfile, updateProfile, updateVehicule }

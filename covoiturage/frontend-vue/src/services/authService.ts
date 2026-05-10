import apiClient from '@/lib/apiClient'
import type { ApiResponse, AuthResponse } from '@/types'

const register = async (payload: {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: 'membre' | 'conducteur'
}): Promise<ApiResponse<AuthResponse>> => {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/register', payload)
  return response.data
}

const registerEtudiant = async (payload: FormData): Promise<ApiResponse<AuthResponse>> => {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/register/etudiant', payload)
  return response.data
}

const registerProfessionnel = async (payload: FormData): Promise<ApiResponse<AuthResponse>> => {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/register/professionnel', payload)
  return response.data
}

const registerConducteur = async (payload: FormData): Promise<ApiResponse<AuthResponse>> => {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/register/conducteur', payload)
  return response.data
}

const login = async (payload: {
  email: string
  password: string
}): Promise<ApiResponse<AuthResponse>> => {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/login', payload)
  return response.data
}

const logout = async (): Promise<ApiResponse<null>> => {
  const response = await apiClient.post<ApiResponse<null>>('/auth/logout')
  return response.data
}

const changePassword = async (payload: { current_password: string; password: string; password_confirmation: string }) : Promise<ApiResponse<null>> => {
  const res = await apiClient.patch<ApiResponse<null>>('/auth/password', payload)
  return res.data
}

export default {
  register,
  registerEtudiant,
  registerProfessionnel,
  registerConducteur,
  login,
  logout,
  changePassword,
}

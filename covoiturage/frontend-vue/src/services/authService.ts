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

export default {
  register,
  login,
  logout,
}

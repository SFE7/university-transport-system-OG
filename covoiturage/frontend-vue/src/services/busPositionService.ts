import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { BusPosition } from '@/types/bus'

const updatePosition = async (payload: {
  latitude: number
  longitude: number
  is_sharing: boolean
}): Promise<ApiResponse<BusPosition>> => {
  const response = await apiClient.patch<ApiResponse<BusPosition>>('/bus/position', payload)
  return response.data
}

const stopSharing = async (): Promise<ApiResponse<BusPosition>> => {
  const response = await apiClient.patch<ApiResponse<BusPosition>>('/bus/position/stop')
  return response.data
}

const getActivePositions = async (): Promise<ApiResponse<BusPosition[]>> => {
  const response = await apiClient.get<ApiResponse<BusPosition[]>>('/bus/positions')
  return response.data
}

export default {
  updatePosition,
  stopSharing,
  getActivePositions,
}

import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { ComparisonResult } from '@/types/bus'

const compare = async (params: {
  departure: string
  arrival: string
  datetime: string
}): Promise<ApiResponse<ComparisonResult>> => {
  const response = await apiClient.get<ApiResponse<ComparisonResult>>('/compare', { params })
  return response.data
}

export default {
  compare,
}

import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { AdminStats } from '@/types/admin'

const fetchStats = async (): Promise<ApiResponse<AdminStats>> => {
  const res = await apiClient.get<ApiResponse<AdminStats>>('/admin/statistiques')
  return res.data
}

export default { fetchStats }

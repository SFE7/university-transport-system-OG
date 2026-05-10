import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { DocumentSoumis } from '@/types/admin'

const fetchPending = async (): Promise<ApiResponse<DocumentSoumis[]>> => {
  const res = await apiClient.get<ApiResponse<DocumentSoumis[]>>('/documents')
  return res.data
}

const approve = async (id: number): Promise<ApiResponse<DocumentSoumis>> => {
  const res = await apiClient.patch<ApiResponse<DocumentSoumis>>(`/documents/${id}/approve`)
  return res.data
}

const reject = async (id: number, reason: string): Promise<ApiResponse<DocumentSoumis>> => {
  const res = await apiClient.patch<ApiResponse<DocumentSoumis>>(`/documents/${id}/reject`, { reason })
  return res.data
}

export default { fetchPending, approve, reject }

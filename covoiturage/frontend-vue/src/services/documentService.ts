import apiClient from '@/lib/apiClient'
import type { ApiResponse } from '@/types'
import type { DocumentSoumis, DocumentStatusResponse } from '@/types/admin'

const fetchPending = async (): Promise<ApiResponse<DocumentSoumis[] | DocumentStatusResponse>> => {
  const res = await apiClient.get<ApiResponse<DocumentSoumis[] | DocumentStatusResponse>>('/documents')
  return res.data
}

const submit = async (payload: FormData): Promise<ApiResponse<DocumentStatusResponse>> => {
  const res = await apiClient.post<ApiResponse<DocumentStatusResponse>>('/documents', payload)
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

const fetchPreviewBlob = async (id: number): Promise<Blob> => {
  const res = await apiClient.get(`/documents/${id}/preview`, {
    responseType: 'blob',
  })

  return res.data
}

export default { fetchPending, submit, approve, reject, fetchPreviewBlob }

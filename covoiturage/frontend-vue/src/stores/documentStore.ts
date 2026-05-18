import { ref } from 'vue'
import { defineStore } from 'pinia'
import documentService from '@/services/documentService'
import type { DocumentSoumis, DocumentStatusResponse } from '@/types/admin'

const extractDocuments = (payload: unknown): DocumentSoumis[] => {
  if (Array.isArray(payload)) {
    return payload as DocumentSoumis[]
  }

  if (!payload || typeof payload !== 'object') {
    return []
  }

  const value = payload as {
    data?: unknown
    documents?: unknown
  }

  if (Array.isArray(value.data)) {
    return value.data as DocumentSoumis[]
  }

  if (value.data && typeof value.data === 'object') {
    const nested = value.data as { data?: unknown; documents?: unknown }

    if (Array.isArray(nested.data)) {
      return nested.data as DocumentSoumis[]
    }

    if (Array.isArray(nested.documents)) {
      return nested.documents as DocumentSoumis[]
    }
  }

  if (Array.isArray(value.documents)) {
    return value.documents as DocumentSoumis[]
  }

  return []
}

export const useDocumentStore = defineStore('document', () => {
  const pending = ref<DocumentSoumis[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchPending = async () => {
    isLoading.value = true
    error.value = null
    try {
      const res = await documentService.fetchPending()
      pending.value = extractDocuments(res.data)
      return res
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to fetch documents'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const approve = async (id: number) => {
    isLoading.value = true
    try {
      const res = await documentService.approve(id)
      // remove from pending
      pending.value = pending.value.filter(d => d.id !== id)
      return res
    } finally {
      isLoading.value = false
    }
  }

  const reject = async (id: number, reason: string) => {
    isLoading.value = true
    try {
      const res = await documentService.reject(id, reason)
      pending.value = pending.value.filter(d => d.id !== id)
      return res
    } finally {
      isLoading.value = false
    }
  }

  return { pending, isLoading, error, fetchPending, approve, reject }
})

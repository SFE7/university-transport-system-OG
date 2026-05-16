import { ref } from 'vue'
import { defineStore } from 'pinia'
import documentService from '@/services/documentService'
import type { DocumentSoumis } from '@/types/admin'

export const useDocumentStore = defineStore('document', () => {
  const pending = ref<DocumentSoumis[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchPending = async () => {
    isLoading.value = true
    error.value = null
    try {
      const res = await documentService.fetchPending()
      pending.value = res.data
      console.log('documents[0]:', pending.value[0])
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

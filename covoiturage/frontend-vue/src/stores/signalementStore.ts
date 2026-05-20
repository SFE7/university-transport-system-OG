import { ref } from 'vue'
import { defineStore } from 'pinia'
import signalementService from '@/services/signalementService'
import type { Signalement } from '@/types/admin'

export const useSignalementStore = defineStore('signalement', () => {
  const list = ref<Signalement[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchAll = async (params?: { status?: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const res = await signalementService.fetchAll(params)
      list.value = res.data
      return res
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to fetch signalements'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: { conducteur_id: number; trajet_id?: number | null; raison: string; description?: string | null }) => {
    isLoading.value = true
    error.value = null
    try {
      const res = await signalementService.create(payload)
      list.value.unshift(res.data)
      return res
    } catch (err: any) {
      error.value = err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})?.flat()?.[0] || 'Failed to create signalement'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const updateStatus = async (id: number, status: string) => {
    isLoading.value = true
    try {
      const res = await signalementService.updateStatus(id, status)
      // update local
      list.value = list.value.map(s => (s.id === id ? res.data : s))
      return res
    } finally {
      isLoading.value = false
    }
  }

  const remove = async (id: number) => {
    isLoading.value = true
    try {
      const res = await signalementService.remove(id)
      list.value = list.value.filter(s => s.id !== id)
      return res
    } finally {
      isLoading.value = false
    }
  }

  return { list, isLoading, error, fetchAll, create, updateStatus, remove }
})

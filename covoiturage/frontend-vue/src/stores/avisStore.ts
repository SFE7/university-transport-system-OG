import { ref } from 'vue'
import { defineStore } from 'pinia'
import avisService from '@/services/avisService'
import type { Avis } from '@/types'

export const useAvisStore = defineStore('avis', () => {
  const avisList = ref<Avis[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchByConducteur = async (membreId: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.getByConducteur(membreId)
      avisList.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: {
    conducteur_id: number
    trajet_id: number
    rating: number
    comment?: string
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.create(payload)
      avisList.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to add avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    avisList,
    isLoading,
    error,
    fetchByConducteur,
    create,
  }
})

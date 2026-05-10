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

  const update = async (id: number, payload: {
    conducteur_id: number
    trajet_id: number
    rating: number
    comment?: string
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.update(id, payload)
      // update local list
      const idx = avisList.value.findIndex((a) => a.id === id)
      if (idx >= 0) avisList.value[idx] = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to update avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const remove = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.delete(id)
      avisList.value = avisList.value.filter((a) => a.id !== id)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to delete avis'
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
    update,
    delete: remove,
  }
})

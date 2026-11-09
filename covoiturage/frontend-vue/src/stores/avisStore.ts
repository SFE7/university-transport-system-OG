import { ref } from 'vue'
import { defineStore } from 'pinia'
import avisService, { type CreateAvisPayload, type UpdateAvisPayload } from '@/services/avisService'
import type { Avis } from '@/types'

export const useAvisStore = defineStore('avis', () => {
  const avis = ref<Avis[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchByConducteur = async (conducteurId: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.getByCondukteur(conducteurId)
      avis.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: CreateAvisPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.create(payload)
      avis.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to add avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const update = async (id: number, payload: UpdateAvisPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await avisService.update(id, payload)
      avis.value = avis.value.map((avisItem) => (avisItem.id === id ? response.data : avisItem))
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
      const response = await avisService.remove(id)
      avis.value = avis.value.filter((avisItem) => avisItem.id !== id)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to delete avis'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    avis,
    isLoading,
    error,
    fetchByConducteur,
    create,
    update,
    remove,
  }
})

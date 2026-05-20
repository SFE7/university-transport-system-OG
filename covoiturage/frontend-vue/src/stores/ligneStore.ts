import { ref } from 'vue'
import { defineStore } from 'pinia'
import ligneService from '@/services/ligneService'
import type { HoraireBus, LigneBus } from '@/types/bus'

export const useLigneStore = defineStore('lignes-bus', () => {
  const lignes = ref<LigneBus[]>([])
  const currentLigne = ref<LigneBus | null>(null)
  const schedules = ref<HoraireBus[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchAll = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.getAll()
      lignes.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load lignes'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchOne = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.getOne(id)
      currentLigne.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load ligne'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchSchedules = async (id: number, day?: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.getSchedules(id, day)
      schedules.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load schedules'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: {
    name: string
    description?: string
    color?: string
    arrets?: Array<{
      name: string
      latitude: number
      longitude: number
      order: number
    }>
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.create(payload)
      lignes.value.push(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to create ligne'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const update = async (id: number, payload: Partial<LigneBus>) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.update(id, payload)
      const idx = lignes.value.findIndex((ligne) => ligne.id === id)
      if (idx >= 0) {
        lignes.value[idx] = response.data
      }
      if (currentLigne.value?.id === id) {
        currentLigne.value = response.data
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to update ligne'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const remove = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await ligneService.delete(id)
      lignes.value = lignes.value.filter((ligne) => ligne.id !== id)
      if (currentLigne.value?.id === id) {
        currentLigne.value = null
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to delete ligne'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    lignes,
    currentLigne,
    schedules,
    isLoading,
    error,
    fetchAll,
    fetchOne,
    fetchSchedules,
    create,
    update,
    delete: remove,
  }
})

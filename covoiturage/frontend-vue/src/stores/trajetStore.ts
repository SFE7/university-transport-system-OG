import { ref } from 'vue'
import { defineStore } from 'pinia'
import trajetService from '@/services/trajetService'
import type { Trajet } from '@/types'

export const useTrajetStore = defineStore('trajets', () => {
  const trajets = ref<Trajet[]>([])
  const currentTrajet = ref<Trajet | null>(null)
  const history = ref<Trajet[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const lastPage = ref(1)

  const fetchAll = async (filters?: {
    departure_point?: string
    arrival_point?: string
    departure_time?: string
    available_seats?: number
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.getAll(filters)
      trajets.value = response.data
      currentPage.value = response.meta?.current_page || 1
      lastPage.value = response.meta?.last_page || 1
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load trajets'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchOne = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.getOne(id)
      currentTrajet.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load trajet'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: {
    departure_point: string
    arrival_point: string
    departure_time: string
    available_seats: number
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.create(payload)
      trajets.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to create trajet'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const cancel = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.cancel(id)
      trajets.value = trajets.value.map((trajet) =>
        trajet.id === id ? { ...trajet, status: 'cancelled' } : trajet
      )
      history.value = history.value.map((trajet) =>
        trajet.id === id ? { ...trajet, status: 'cancelled' } : trajet
      )
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to cancel trajet'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchHistory = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.getHistory()
      history.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load history'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    trajets,
    currentTrajet,
    history,
    isLoading,
    error,
    currentPage,
    lastPage,
    fetchAll,
    fetchOne,
    create,
    cancel,
    fetchHistory,
  }
})

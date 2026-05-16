import { ref } from 'vue'
import { defineStore } from 'pinia'
import trajetService from '@/services/trajetService'
import type { Trajet } from '@/types'

type CreateTrajetPayload = {
  departure_point: string
  arrival_point: string
  departure_time: string
  available_seats: number
  car_category: string
  car_model: string
  car_photo_url?: string | null
  carPhotoFile?: File | null
}

export const useTrajetStore = defineStore('trajets', () => {
  const trajets = ref<Trajet[]>([])
  const myTrajets = ref<Trajet[]>([])
  const currentTrajet = ref<Trajet | null>(null)
  const history = ref<Trajet[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const currentPage = ref(1)
  const lastPage = ref(1)
  const selectedCategory = ref('')
  const selectedModel = ref('')
  const carPhotoFile = ref<File | null>(null)
  const carPhotoUrl = ref<string | null>(null)

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

  const create = async (payload: CreateTrajetPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const requestPayload = {
        ...payload,
        car_category: payload.car_category || selectedCategory.value,
        car_model: payload.car_model || selectedModel.value,
        car_photo_url: payload.car_photo_url ?? carPhotoUrl.value,
        carPhotoFile: payload.carPhotoFile ?? carPhotoFile.value,
      }

      console.log('[trajetStore.create] forwarding payload', requestPayload)

      const response = await trajetService.create(requestPayload)
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

  const fetchMy = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await trajetService.getMine()
      myTrajets.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load my trajets'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    trajets,
    myTrajets,
    currentTrajet,
    history,
    isLoading,
    error,
    currentPage,
    lastPage,
    selectedCategory,
    selectedModel,
    carPhotoFile,
    carPhotoUrl,
    fetchAll,
    fetchOne,
    create,
    cancel,
    fetchHistory,
    fetchMy,
  }
})

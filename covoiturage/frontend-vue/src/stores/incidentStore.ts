import { ref } from 'vue'
import { defineStore } from 'pinia'
import incidentService from '@/services/incidentService'
import type { IncidentBus } from '@/types/bus'

export const useIncidentStore = defineStore('incidents-bus', () => {
  const incidents = ref<IncidentBus[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchAll = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await incidentService.getAll()
      incidents.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load incidents'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: { ligne_bus_id: number; type: string; description: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await incidentService.create(payload)
      incidents.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to create incident'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const resolve = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await incidentService.resolve(id)
      const idx = incidents.value.findIndex((item) => item.id === id)
      if (idx >= 0) {
        const target = incidents.value[idx]
        if (target) {
          target.resolved_at = new Date().toISOString()
        }
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to resolve incident'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const remove = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await incidentService.delete(id)
      incidents.value = incidents.value.filter((item) => item.id !== id)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to delete incident'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    incidents,
    isLoading,
    error,
    fetchAll,
    create,
    resolve,
    delete: remove,
  }
})

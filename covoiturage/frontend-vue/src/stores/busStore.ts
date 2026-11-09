import { ref } from 'vue'
import { defineStore } from 'pinia'
import echo from '@/lib/echo'
import busPositionService from '@/services/busPositionService'
import type { BusPosition } from '@/types/bus'

export const useBusStore = defineStore('bus', () => {
  const positions = ref<BusPosition[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchActivePositions = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await busPositionService.getActivePositions()
      positions.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load active bus positions'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const updateMyPosition = async (payload: {
    latitude: number
    longitude: number
    is_sharing: boolean
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await busPositionService.updatePosition(payload)
      const idx = positions.value.findIndex((p) => p.chauffeur_id === response.data.chauffeur_id)
      if (idx >= 0) {
        positions.value[idx] = response.data
      } else {
        positions.value.push(response.data)
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to update bus position'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const stopSharing = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await busPositionService.stopSharing()
      const idx = positions.value.findIndex((p) => p.chauffeur_id === response.data.chauffeur_id)
      if (idx >= 0) {
        positions.value[idx] = response.data
      }
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to stop sharing'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const subscribeToDriver = (chauffeurId: number) => {
    echo
      .channel('bus.' + chauffeurId)
      .listen('BusPositionUpdated', (data: BusPosition) => {
        const idx = positions.value.findIndex((p) => p.chauffeur_id === data.chauffeur_id)
        if (idx >= 0) {
          positions.value[idx] = data
        } else {
          positions.value.push(data)
        }
      })
  }

  const subscribeToAlerts = (membreId: number, onAlert: (data: any) => void) => {
    echo.private('membre.' + membreId).listen('BusApproachingAlert', onAlert)
  }

  return {
    positions,
    isLoading,
    error,
    fetchActivePositions,
    updateMyPosition,
    stopSharing,
    subscribeToDriver,
    subscribeToAlerts,
  }
})

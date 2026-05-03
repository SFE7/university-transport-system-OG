import { ref } from 'vue'
import { defineStore } from 'pinia'
import comparisonService from '@/services/comparisonService'
import type { ComparisonResult } from '@/types/bus'

export const useComparisonStore = defineStore('comparison', () => {
  const result = ref<ComparisonResult | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const compare = async (params: { departure: string; arrival: string; datetime: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await comparisonService.compare(params)
      result.value = response.data
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to compare options'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    result,
    isLoading,
    error,
    compare,
  }
})

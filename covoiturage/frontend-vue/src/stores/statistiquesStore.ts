import { ref } from 'vue'
import { defineStore } from 'pinia'
import statistiquesService from '@/services/statistiquesService'
import type { AdminStats } from '@/types/admin'

export const useStatistiquesStore = defineStore('statistiques', () => {
  const stats = ref<AdminStats | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const extractStats = (payload: unknown): AdminStats | null => {
    if (!payload || typeof payload !== 'object') {
      return null
    }

    const value = payload as { data?: unknown }

    if (value.data && typeof value.data === 'object' && !Array.isArray(value.data)) {
      return value.data as AdminStats
    }

    return payload as AdminStats
  }

  const fetchStats = async () => {
    isLoading.value = true
    error.value = null
    try {
      const res = await statistiquesService.fetchStats()
      stats.value = extractStats(res.data)
      return res
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load statistics'
      stats.value = null
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return { stats, isLoading, error, fetchStats }
})

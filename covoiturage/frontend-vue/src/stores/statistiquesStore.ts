import { ref } from 'vue'
import { defineStore } from 'pinia'
import statistiquesService from '@/services/statistiquesService'
import type { AdminStats } from '@/types/admin'

export const useStatistiquesStore = defineStore('statistiques', () => {
  const stats = ref<AdminStats | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchStats = async () => {
    isLoading.value = true
    try {
      const res = await statistiquesService.fetchStats()
      stats.value = res.data
      return res
    } finally {
      isLoading.value = false
    }
  }

  return { stats, isLoading, error, fetchStats }
})

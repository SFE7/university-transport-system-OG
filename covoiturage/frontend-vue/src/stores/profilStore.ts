import { ref } from 'vue'
import { defineStore } from 'pinia'
import profilService from '@/services/profilService'
import type { Membre } from '@/types'
import type { Vehicule } from '@/types/admin'

export const useProfilStore = defineStore('profil', () => {
  const profile = ref<Membre | null>(null)
  const vehicule = ref<Vehicule | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchProfile = async (id: number) => {
    isLoading.value = true
    try {
      const res = await profilService.fetchProfile(id)
      profile.value = res.data
      return res
    } finally {
      isLoading.value = false
    }
  }

  const updateProfile = async (payload: Partial<Membre>) => {
    isLoading.value = true
    try {
      const res = await profilService.updateProfile(payload)
      profile.value = res.data
      return res
    } finally {
      isLoading.value = false
    }
  }

  const updateVehicule = async (payload: { marque: string; modele: string; immatriculation: string; couleur?: string }) => {
    isLoading.value = true
    try {
      const res = await profilService.updateVehicule(payload)
      vehicule.value = res.data
      return res
    } finally {
      isLoading.value = false
    }
  }

  return { profile, vehicule, isLoading, error, fetchProfile, updateProfile, updateVehicule }
})

import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import authService from '@/services/authService'
import type { Membre } from '@/types'

const TOKEN_KEY = 'auth_token'
const MEMBRE_KEY = 'auth_membre'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const membre = ref<Membre | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value))
  const isConducteur = computed(() => membre.value?.role === 'conducteur')

  const register = async (payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    role: 'membre' | 'conducteur'
  }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.register(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerEtudiant = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.registerEtudiant(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerProfessionnel = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.registerProfessionnel(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerConducteur = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.registerConducteur(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const login = async (payload: { email: string; password: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.login(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    isLoading.value = true
    error.value = null
    try {
      await authService.logout()
    } catch (err) {
      error.value = 'Logout failed'
    } finally {
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(MEMBRE_KEY)
      token.value = null
      membre.value = null
      isLoading.value = false
    }
  }

  const changePassword = async (payload: { current_password: string; password: string; password_confirmation: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.changePassword(payload)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Change password failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const initFromStorage = () => {
    const storedToken = localStorage.getItem(TOKEN_KEY)
    const storedMembre = localStorage.getItem(MEMBRE_KEY)
    token.value = storedToken || null
    if (storedMembre) {
      try {
        membre.value = JSON.parse(storedMembre) as Membre
      } catch {
        membre.value = null
      }
    }
  }

  return {
    token,
    membre,
    isLoading,
    error,
    register,
    registerEtudiant,
    registerProfessionnel,
    registerConducteur,
    login,
    logout,
    changePassword,
    initFromStorage,
    isAuthenticated,
    isConducteur,
  }
})

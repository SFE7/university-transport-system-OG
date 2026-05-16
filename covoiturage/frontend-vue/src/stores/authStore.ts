import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import authService from '@/services/authService'
import apiClient from '@/lib/apiClient'
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
  const validationErrors = ref<Record<string, string[]> | null>(null)

  const role = computed<"membre" | "conducteur" | "chauffeur_bus" | "admin" | null>(() => {
    // Unwrap `membre` safely: Pinia sometimes exposes a plain object or a ref-like proxy.
    // Return `null` (not undefined) when no membre/role is available so guard checks like `if (!role)` behave predictably.
    const candidate = membre as unknown

    // If we received a ref-like object with a `value` property, extract it.
    if (typeof candidate === 'object' && candidate !== null && 'value' in (candidate as Record<string, unknown>)) {
      const inner = (candidate as { value: unknown }).value as Membre | null | undefined
      if (inner && typeof inner === 'object' && 'role' in inner) {
        return inner.role as "membre" | "conducteur" | "chauffeur_bus" | "admin"
      }
      return null
    }

    // Otherwise, if it's already a plain object with `role`, use it.
    if (typeof candidate === 'object' && candidate !== null && 'role' in (candidate as Record<string, unknown>)) {
      return (candidate as Membre).role as "membre" | "conducteur" | "chauffeur_bus" | "admin"
    }

    // No membre available.
    return null
  })

  const register = async (payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    role: 'membre' | 'conducteur'
  }) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = null
    try {
      const response = await authService.register(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      validationErrors.value = err?.response?.data?.errors ?? null
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerEtudiant = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = null
    try {
      const response = await authService.registerEtudiant(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      validationErrors.value = err?.response?.data?.errors ?? null
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerProfessionnel = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = null
    try {
      const response = await authService.registerProfessionnel(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      validationErrors.value = err?.response?.data?.errors ?? null
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const registerConducteur = async (payload: FormData) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = null
    try {
      const response = await authService.registerConducteur(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      validationErrors.value = err?.response?.data?.errors ?? null
      error.value = err?.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const login = async (payload: { email: string; password: string }) => {
    isLoading.value = true
    error.value = null
    validationErrors.value = null
    try {
      const response = await authService.login(payload)
      token.value = response.data.token
      membre.value = response.data.membre
      localStorage.setItem(TOKEN_KEY, response.data.token)
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(response.data.membre))
      return response
    } catch (err: any) {
      validationErrors.value = err?.response?.data?.errors ?? null
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

  const verifyToken = async (): Promise<boolean> => {
    if (!token.value) return false
    try {
      const response = await apiClient.get('/auth/me')
      const membrePayload = response.data?.data ?? response.data
      membre.value = membrePayload as Membre
      localStorage.setItem(MEMBRE_KEY, JSON.stringify(membrePayload))
      return true
    } catch {
      token.value = null
      membre.value = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(MEMBRE_KEY)
      return false
    }
  }

  return {
    token,
    membre,
    isLoading,
    error,
    validationErrors,
    register,
    registerEtudiant,
    registerProfessionnel,
    registerConducteur,
    login,
    logout,
    changePassword,
    initFromStorage,
    verifyToken,
    isAuthenticated,
    isConducteur,
    role,
  }
})

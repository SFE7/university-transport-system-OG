import { ref } from 'vue'
import { defineStore } from 'pinia'
import reservationService from '@/services/reservationService'
import type { Reservation } from '@/types'

export const useReservationStore = defineStore('reservations', () => {
  const reservations = ref<Reservation[]>([])
  const pendingDemandes = ref<Reservation[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchMyReservations = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.getMyReservations()
      const payload = response.data as unknown
      const normalized = Array.isArray(payload)
        ? payload
        : Array.isArray((payload as { data?: unknown })?.data)
          ? (payload as { data: Reservation[] }).data
          : []

      reservations.value = normalized as Reservation[]
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load reservations'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const create = async (payload: { trajet_id: number }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.create(payload)
      if (!Array.isArray(reservations.value)) {
        reservations.value = []
      }
      reservations.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to create reservation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const fetchMyDemandes = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.getMyDemandes()
      const payload = response.data as unknown
      const normalized = Array.isArray(payload)
        ? payload
        : Array.isArray((payload as { data?: unknown })?.data)
          ? (payload as { data: Reservation[] }).data
          : []

      pendingDemandes.value = normalized as Reservation[]
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to load demandes'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const cancel = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.cancel(id)
      reservations.value = reservations.value.map((reservation) =>
        reservation.id === id ? { ...reservation, status: 'cancelled' } : reservation
      )
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to cancel reservation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const accept = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.accept(id)
      reservations.value = reservations.value.map((reservation) =>
        reservation.id === id ? response.data : reservation
      )
      pendingDemandes.value = pendingDemandes.value.filter((reservation) => reservation.id !== id)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to accept reservation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  const refuse = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.refuse(id)
      reservations.value = reservations.value.map((reservation) =>
        reservation.id === id ? response.data : reservation
      )
      pendingDemandes.value = pendingDemandes.value.filter((reservation) => reservation.id !== id)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to refuse reservation'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    reservations,
    pendingDemandes,
    isLoading,
    error,
    fetchMyReservations,
    fetchMyDemandes,
    create,
    cancel,
    accept,
    refuse,
  }
})

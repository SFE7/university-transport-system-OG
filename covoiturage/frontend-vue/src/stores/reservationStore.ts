import { ref } from 'vue'
import { defineStore } from 'pinia'
import reservationService from '@/services/reservationService'
import type { Reservation } from '@/types'

export const useReservationStore = defineStore('reservations', () => {
  const reservations = ref<Reservation[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchMyReservations = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await reservationService.getMyReservations()
      reservations.value = response.data
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
      reservations.value.unshift(response.data)
      return response
    } catch (err: any) {
      error.value = err?.response?.data?.message || 'Failed to create reservation'
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
    isLoading,
    error,
    fetchMyReservations,
    create,
    cancel,
    accept,
    refuse,
  }
})

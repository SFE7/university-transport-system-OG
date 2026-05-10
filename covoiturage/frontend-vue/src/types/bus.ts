export interface LigneBus {
  id: number
  name: string
  description: string | null
  is_active: boolean
  arrets?: ArretBus[]
  horaires?: HoraireBus[]
}

export interface ArretBus {
  id: number
  name: string
  latitude: number
  longitude: number
  order: number
  ligne_bus_id: number
}

export interface HoraireBus {
  id: number
  ligne_bus_id: number
  departure_time: string
  days: string[]
  is_active: boolean
  chauffeur_id?: number
  chauffeur?: import('./index').Membre | null
}

export interface BusPosition {
  chauffeur_id: number
  latitude: number
  longitude: number
  is_sharing: boolean
  updated_at: string
}

export interface IncidentBus {
  id: number
  ligne_bus_id: number
  type: 'delay' | 'breakdown' | 'cancelled' | 'other'
  description: string
  resolved_at: string | null
}

export interface ComparisonResult {
  covoiturage: import('./index').Trajet[]
  bus: HoraireBus[]
  summary: {
    fastest: 'bus' | 'covoiturage'
    cheapest: 'bus' | 'covoiturage'
  }
}

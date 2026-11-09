export interface Membre {
  id: number
  name: string
  email: string
  role: 'membre' | 'conducteur' | 'chauffeur_bus' | 'admin'
  phone: string | null
  account_type: 'etudiant' | 'professionnel' | null
  has_verified_documents: boolean
  is_suspended?: boolean
  is_banned?: boolean
  created_at: string
}

export interface Trajet {
  id: number
  departure_point: string
  arrival_point: string
  departure_time: string
  available_seats: number
  car_category: string | null
  car_model: string | null
  car_photo_url: string | null
  status: 'active' | 'full' | 'cancelled' | 'completed'
  membre_id: number
  conducteur: Membre | null
  created_at: string
}

export interface Reservation {
  id: number
  membre_id: number
  trajet_id: number
  status: 'pending' | 'accepted' | 'refused' | 'cancelled'
  membre?: Membre | null
  trajet: Trajet | null
  created_at: string
}

export interface Avis {
  id: number
  conducteur_id: number
  trajet_id: number
  note: number
  commentaire: string
  membre_id: number
  created_at: string
}

export interface Notification {
  id: number
  membre_id: number
  message: string
  type: string
  is_read: boolean
  created_at: string
}

export interface ApiResponse<T> {
  data: T
  message: string
  status: number
}

export interface PaginatedResponse<T> {
  data: T[]
  message: string
  status: number
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface AuthResponse {
  token: string
  membre: Membre
}

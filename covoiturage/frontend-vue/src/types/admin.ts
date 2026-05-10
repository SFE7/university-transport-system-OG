import type { Membre } from './index'

export interface DocumentSoumis {
  id: number
  membre_id: number
  type: 'carte_etudiante' | 'carte_identite' | 'permis_conduire' | 'carte_grise'
  file_path: string
  status: 'en_attente' | 'approuve' | 'rejete'
  rejection_reason: string | null
  reviewed_at: string | null
  membre?: Membre
}

export interface Signalement {
  id: number
  reporter: Membre
  reported: Membre
  reason: string
  status: 'en_attente' | 'traite' | 'archive'
  created_at: string
}

export interface Vehicule {
  id: number
  conducteur_id: number
  marque: string
  modele: string
  immatriculation: string
  couleur: string | null
}

export interface AdminStats {
  membres: { total: number; actifs: number; bannis: number; par_role: { role: string; total: number }[] }
  trajets: { total: number; actifs: number; complets: number; annules: number }
  reservations: { total: number; en_attente: number; acceptees: number }
  bus: { lignes: number; chauffeurs: number; incidents: number }
  signalements: { total: number; en_attente: number }
  documents: { en_attente: number; approuves: number; rejetes: number }
}

export interface RegisterEtudiantPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  carte_etudiante: File
}

export interface RegisterProfessionnelPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  carte_identite: File
}

export interface RegisterConducteurPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  permis_conduire: File
  carte_grise: File
}

export interface ChangePasswordPayload {
  current_password: string
  password: string
  password_confirmation: string
}

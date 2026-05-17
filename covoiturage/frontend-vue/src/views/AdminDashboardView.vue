<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLigneStore } from '@/stores/ligneStore'
import { useIncidentStore } from '@/stores/incidentStore'
import { useDocumentStore } from '@/stores/documentStore'
import { useSignalementStore } from '@/stores/signalementStore'
import { useStatistiquesStore } from '@/stores/statistiquesStore'
import adminService from '@/services/adminService'
import arretService from '@/services/arretService'
import apiClient from '@/lib/apiClient'
import type { Membre } from '@/types'
import type { HoraireBus } from '@/types/bus'

type AdminHoraire = HoraireBus & {
  ligne?: {
    id: number
    name: string
  } | null
}

const route = useRoute()
const router = useRouter()
const ligneStore = useLigneStore()
const incidentStore = useIncidentStore()
const documentStore = useDocumentStore()
const signalementStore = useSignalementStore()
const statistiquesStore = useStatistiquesStore()

const tab = ref<'lignes' | 'incidents' | 'chauffeurs' | 'arrets' | 'horaires' | 'documents' | 'signalements' | 'membres' | 'statistiques'>('lignes')
const expandedLigne = ref<number | null>(null)

const ligneForm = reactive({ name: '', description: '', color: '#00c853', arrets: [] as Array<{ name: string; latitude: string; longitude: string; order: number }> })
const incidentForm = reactive({ ligne_bus_id: 0, type: 'other', description: '' })
const arretForm = reactive({ name: '', latitude: '', longitude: '', order: 0, ligne_bus_id: 0 })
const editingArretId = ref<number | null>(null)

const horaireForm = reactive({ ligne_bus_id: 0, chauffeur_id: 0, departure_time: '' })
const horaireDaysInput = ref('')
const horaires = ref<AdminHoraire[]>([])
const horaireError = ref<string | null>(null)
const editingHoraireId = ref<number | null>(null)

const membres = ref<Membre[]>([])
const chauffeurs = ref<Membre[]>([])
const membreError = ref<string | null>(null)
const chauffeurError = ref<string | null>(null)
const chauffeurForm = reactive({ name: '', email: '', password: '', password_confirmation: '' })

watch(membres, (val) => console.log('membres changed:', val), { immediate: true })

const rejectReason = ref('')
const rejectingDocId = ref<number | null>(null)
const newArretForms = reactive<Record<number, { name: string; latitude: string; longitude: string }>>({})
const editingInlineArret = reactive<Record<number, { id: number; name: string; latitude: string; longitude: string; order: number } | null>>({})
const ligneColorDrafts = reactive<Record<number, string>>({})

const chauffeurRows = computed(() =>
  chauffeurs.value
)

const stats = computed(() => statistiquesStore.stats)

const numberFormatter = new Intl.NumberFormat('fr-FR')

const formatCount = (value: number) => numberFormatter.format(value)

const formatPercent = (value: number) => `${Math.round(value)}%`

const trendGlyph = (trend: 'up' | 'down' | 'flat') => {
  if (trend === 'up') return '↗'
  if (trend === 'down') return '↘'
  return '→'
}

const formatSignalementDate = (value: string | null | undefined) => {
  if (!value) return 'Date inconnue'

  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

const chartRoleLabels: Record<string, string> = {
  membre: 'Membres',
  conducteur: 'Conducteurs',
  chauffeur_bus: 'Ch. bus',
  admin: 'Admins',
}

const chartRoleColors: Record<string, string> = {
  membre: '#ffe180',
  conducteur: '#c8b37a',
  chauffeur_bus: '#9f9065',
  admin: '#f7c85f',
}

const statsCards = computed(() => {
  const current = stats.value
  const totalUsers = current?.membres?.total ?? 0
  const activeUsers = current?.membres?.actifs ?? 0
  const bannedUsers = current?.membres?.bannis ?? 0
  const trajetsTotal = current?.trajets?.total ?? 0
  const trajetsActifs = current?.trajets?.actifs ?? 0
  const trajetsComplets = current?.trajets?.complets ?? 0
  const reservationsTotal = current?.reservations?.total ?? 0
  const reservationsPending = current?.reservations?.en_attente ?? 0
  const reservationsAccepted = current?.reservations?.acceptees ?? 0
  const busLines = current?.bus?.lignes ?? 0
  const busDrivers = current?.bus?.chauffeurs ?? 0
  const incidents = current?.bus?.incidents ?? 0
  const docsPending = current?.documents?.en_attente ?? 0
  const docsApproved = current?.documents?.approuves ?? 0

  return [
    {
      key: 'users',
      label: 'Utilisateurs',
      value: totalUsers,
      detail: `${formatCount(activeUsers)} actifs • ${formatCount(bannedUsers)} bannis`,
      icon: '👥',
      trend: activeUsers >= bannedUsers ? 'up' : 'down',
      trendLabel: activeUsers >= bannedUsers ? 'Santé globale' : 'Surveillance',
      accent: '#ffe180',
      gradient: 'linear-gradient(135deg, rgba(255, 225, 128, 0.22), rgba(27, 61, 47, 0.96))',
    },
    {
      key: 'trajets',
      label: 'Trajets',
      value: trajetsTotal,
      detail: `${formatCount(trajetsActifs)} actifs • ${formatCount(trajetsComplets)} terminés`,
      icon: '🛣️',
      trend: trajetsTotal > 0 ? 'up' : 'flat',
      trendLabel: 'Activité',
      accent: '#c8b37a',
      gradient: 'linear-gradient(135deg, rgba(200, 179, 122, 0.25), rgba(27, 61, 47, 0.96))',
    },
    {
      key: 'reservations',
      label: 'Réservations',
      value: reservationsTotal,
      detail: `${formatCount(reservationsPending)} en attente • ${formatCount(reservationsAccepted)} acceptées`,
      icon: '🎫',
      trend: reservationsPending > reservationsAccepted ? 'down' : reservationsAccepted > 0 ? 'up' : 'flat',
      trendLabel: 'Flux',
      accent: '#f7c85f',
      gradient: 'linear-gradient(135deg, rgba(247, 200, 95, 0.22), rgba(27, 61, 47, 0.96))',
    },
    {
      key: 'bus',
      label: 'Lignes bus',
      value: busLines,
      detail: `${formatCount(busDrivers)} chauffeurs • mobilité active`,
      icon: '🚌',
      trend: busDrivers > 0 ? 'up' : 'flat',
      trendLabel: 'Réseau',
      accent: '#9f9065',
      gradient: 'linear-gradient(135deg, rgba(159, 144, 101, 0.28), rgba(27, 61, 47, 0.96))',
    },
    {
      key: 'incidents',
      label: 'Incidents',
      value: incidents,
      detail: 'À traiter rapidement',
      icon: '⚠️',
      trend: incidents > 0 ? 'down' : 'flat',
      trendLabel: 'À réduire',
      accent: '#ff9e7a',
      gradient: 'linear-gradient(135deg, rgba(255, 158, 122, 0.22), rgba(27, 61, 47, 0.96))',
    },
    {
      key: 'documents',
      label: 'Docs en attente',
      value: docsPending,
      detail: `${formatCount(docsApproved)} approuvés • revue admin`,
      icon: '📄',
      trend: docsPending > 0 ? 'down' : 'flat',
      trendLabel: 'Validation',
      accent: '#d7d0a7',
      gradient: 'linear-gradient(135deg, rgba(215, 208, 167, 0.22), rgba(27, 61, 47, 0.96))',
    },
  ]
})

const userRoleBars = computed(() => {
  const roleEntries = stats.value?.membres?.par_role ?? []
  const orderedRoles = ['membre', 'conducteur', 'chauffeur_bus', 'admin']
  const mapped = roleEntries
    .map((entry) => ({
      key: entry.role,
      label: chartRoleLabels[entry.role] ?? entry.role.replace(/_/g, ' '),
      value: entry.total,
      color: chartRoleColors[entry.role] ?? '#ffe180',
    }))
    .sort((left, right) => {
      const leftIndex = orderedRoles.indexOf(left.key)
      const rightIndex = orderedRoles.indexOf(right.key)
      return (leftIndex === -1 ? orderedRoles.length : leftIndex) - (rightIndex === -1 ? orderedRoles.length : rightIndex)
    })

  if (mapped.length > 0) return mapped

  return [
    {
      key: 'total',
      label: 'Utilisateurs',
      value: stats.value?.membres?.total ?? 0,
      color: '#ffe180',
    },
  ]
})

const reservationSegments = computed(() => {
  const current = stats.value?.reservations
  const segments = [
    { key: 'pending', label: 'En attente', value: current?.en_attente ?? 0, color: '#ffe180' },
    { key: 'accepted', label: 'Acceptées', value: current?.acceptees ?? 0, color: '#9f9065' },
  ].filter((segment) => segment.value > 0)

  if (segments.length > 0) return segments

  return [{ key: 'total', label: 'Total', value: current?.total ?? 0, color: '#ffe180' }]
})

const reservationTotal = computed(() => reservationSegments.value.reduce((sum, segment) => sum + segment.value, 0))

const reservationGradient = computed(() => {
  const segments = reservationSegments.value
  const total = reservationTotal.value

  if (!total) {
    return 'conic-gradient(rgba(255, 225, 128, 0.16) 0deg 360deg)'
  }

  let cursor = 0
  const stops = segments.map((segment) => {
    const start = (cursor / total) * 360
    cursor += segment.value
    const end = (cursor / total) * 360
    return `${segment.color} ${start}deg ${end}deg`
  })

  return `conic-gradient(${stops.join(', ')})`
})

const reservationLegend = computed(() => {
  const total = reservationTotal.value || 1
  return reservationSegments.value.map((segment) => ({
    ...segment,
    percent: (segment.value / total) * 100,
  }))
})

const activityBars = computed(() =>
  statsCards.value.map((card) => ({
    key: card.key,
    label: card.label,
    shortLabel: card.label === 'Docs en attente' ? 'Docs' : card.label,
    value: card.value,
    color: card.accent,
  }))
)

const roleChartMax = computed(() => Math.max(...userRoleBars.value.map((bar) => bar.value), 1))
const activityChartMax = computed(() => Math.max(...activityBars.value.map((bar) => bar.value), 1))
const roleChartTicks = computed(() => Array.from({ length: 4 }, (_, index) => Math.round((roleChartMax.value * (4 - index)) / 4)))
const activityChartTicks = computed(() => Array.from({ length: 4 }, (_, index) => Math.round((activityChartMax.value * (4 - index)) / 4)))
const roleSlotWidth = computed(() => 420 / Math.max(userRoleBars.value.length, 1))
const activitySlotWidth = computed(() => 700 / Math.max(activityBars.value.length, 1))
const activityChartTop = 42
const activityChartStep = 58
const activityChartBaseline = 276
const activityChartHeight = 168

const DAY_ALIASES: Record<string, string> = {
  monday: 'monday',
  lundi: 'monday',
  lun: 'monday',
  tuesday: 'tuesday',
  mardi: 'tuesday',
  mar: 'tuesday',
  wednesday: 'wednesday',
  mercredi: 'wednesday',
  mer: 'wednesday',
  thursday: 'thursday',
  jeudi: 'thursday',
  jeu: 'thursday',
  friday: 'friday',
  vendredi: 'friday',
  ven: 'friday',
  saturday: 'saturday',
  samedi: 'saturday',
  sam: 'saturday',
  sunday: 'sunday',
  dimanche: 'sunday',
  dim: 'sunday',
}

const ENGLISH_TO_FRENCH: Record<string, string> = {
  monday: 'lundi',
  tuesday: 'mardi',
  wednesday: 'mercredi',
  thursday: 'jeudi',
  friday: 'vendredi',
  saturday: 'samedi',
  sunday: 'dimanche',
}

const normalizeDaysInput = (input: string): string[] => {
  const values = input
    .split(',')
    .map((value) => value.trim())
    .map((value) => value.toLowerCase())
    .map((value) => DAY_ALIASES[value])
    .filter((value): value is string => Boolean(value))

  return Array.from(new Set(values))
}

const resetHoraireForm = () => {
  editingHoraireId.value = null
  horaireForm.ligne_bus_id = 0
  horaireForm.chauffeur_id = 0
  horaireForm.departure_time = ''
  horaireDaysInput.value = ''
}

const loadHoraires = async () => {
  const horairesResponse = await adminService.getHoraires()
  horaires.value = horairesResponse.data
}

const loadHoraireTabData = async () => {
  const responses = await Promise.all([
    adminService.getChauffeurs(),
    adminService.getHoraires(),
    ligneStore.fetchAll(),
  ])
  const chauffeurResp = responses[0]
  const horairesResp = responses[1]
  chauffeurs.value = chauffeurResp.data
  horaires.value = horairesResp.data
}

const loadAll = async () => {
  try {
    console.log('loadAll called')
    await Promise.all([ligneStore.fetchAll(), incidentStore.fetchAll()])
    const [membResp, chauffeurResp] = await Promise.all([
      adminService.getMembres(),
      adminService.getChauffeurs(),
    ])
    console.log('membResp raw:', membResp)
    membres.value = membResp.data
    console.log('membres.value after assign:', membres.value)
    chauffeurs.value = chauffeurResp.data
  } catch (err) {
    console.error('Error loading admin data:', err)
    throw err
  }
}

const validTabs = ['lignes', 'incidents', 'chauffeurs', 'arrets', 'horaires', 'documents', 'signalements', 'membres', 'statistiques'] as const

const resolveTabFromRoute = () => {
  const queryTab = route.query.tab
  if (typeof queryTab === 'string' && validTabs.includes(queryTab as typeof validTabs[number])) {
    return queryTab as typeof validTabs[number]
  }

  const pathSegment = route.path.split('/')[2]
  if (pathSegment && validTabs.includes(pathSegment as typeof validTabs[number])) {
    return pathSegment as typeof validTabs[number]
  }

  return 'lignes' as const
}

// Watch both the legacy query param and the new path-based routes for tab selection.
watch(() => [route.path, route.query.tab], () => {
  tab.value = resolveTabFromRoute() as any
}, { immediate: true })

// Watch tab changes to update route
watch(tab, async (newTab) => {
  router.push(newTab === 'lignes' ? '/admin/lignes' : `/admin/${newTab}`)
  if (newTab === 'horaires') {
    try {
      await loadHoraireTabData()
    } catch (err: any) {
      horaireError.value = err?.response?.data?.message || 'Erreur de chargement des horaires'
    }
  }
}, { immediate: false })

const submitArret = async () => {
  const payload = {
    name: arretForm.name,
    latitude: Number(arretForm.latitude),
    longitude: Number(arretForm.longitude),
    order: Number(arretForm.order),
    ligne_bus_id: Number(arretForm.ligne_bus_id),
  }
  if (editingArretId.value) {
    await arretService.update(editingArretId.value, payload)
    editingArretId.value = null
  } else {
    await arretService.create(payload)
  }
  arretForm.name = ''
  arretForm.latitude = ''
  arretForm.longitude = ''
  arretForm.order = 0
  arretForm.ligne_bus_id = 0
  await ligneStore.fetchAll()
}

const editArret = (arret: any) => {
  editingArretId.value = arret.id
  arretForm.name = arret.name
  arretForm.latitude = String(arret.latitude)
  arretForm.longitude = String(arret.longitude)
  arretForm.order = arret.order
  arretForm.ligne_bus_id = arret.ligne_bus_id
}

const deleteArret = async (id: number) => {
  if (!confirm('Confirmer suppression de cet arret ?')) return
  await arretService.delete(id)
  await ligneStore.fetchAll()
}

const getSortedArrets = (ligne: any) => (ligne?.arrets || []).slice().sort((a: any, b: any) => a.order - b.order)

const getLigneColorDraft = (ligne: any) => {
  const fallback = ligne?.color || '#00c853'
  if (!ligneColorDrafts[ligne.id]) {
    ligneColorDrafts[ligne.id] = fallback
  }
  return ligneColorDrafts[ligne.id]
}

const saveLigneColor = async (ligne: any) => {
  const color = ligneColorDrafts[ligne.id] || '#00c853'
  try {
    await ligneStore.update(ligne.id, { color })
    await ligneStore.fetchAll()
  } catch (err) {
    console.error('Erreur mise a jour couleur:', err)
    alert('Impossible de sauvegarder la couleur de la ligne.')
  }
}

const ensureNewArretForm = (ligneId: number) => {
  if (!newArretForms[ligneId]) {
    newArretForms[ligneId] = { name: '', latitude: '', longitude: '' }
  }
  return newArretForms[ligneId]
}

const addInlineArret = async (ligneId: number) => {
  const form = ensureNewArretForm(ligneId)
  if (!form.name || !form.latitude || !form.longitude) {
    alert('Veuillez remplir nom, latitude et longitude')
    return
  }

  const ligne = ligneStore.lignes.find((l) => l.id === ligneId)
  const maxOrder = (ligne?.arrets || []).reduce((max, a) => Math.max(max, Number(a.order) || 0), -1)

  await arretService.create({
    name: form.name,
    latitude: Number(form.latitude),
    longitude: Number(form.longitude),
    order: maxOrder + 1,
    ligne_bus_id: ligneId,
  })

  newArretForms[ligneId] = { name: '', latitude: '', longitude: '' }
  await ligneStore.fetchAll()
}

const startEditInlineArret = (ligneId: number, arret: any) => {
  editingInlineArret[ligneId] = {
    id: arret.id,
    name: arret.name,
    latitude: String(arret.latitude),
    longitude: String(arret.longitude),
    order: Number(arret.order),
  }
}

const cancelEditInlineArret = (ligneId: number) => {
  editingInlineArret[ligneId] = null
}

const saveInlineArret = async (ligneId: number) => {
  const editing = editingInlineArret[ligneId]
  if (!editing) return

  await arretService.update(editing.id, {
    name: editing.name,
    latitude: Number(editing.latitude),
    longitude: Number(editing.longitude),
    order: editing.order,
    ligne_bus_id: ligneId,
  })

  editingInlineArret[ligneId] = null
  await ligneStore.fetchAll()
}

const submitHoraire = async () => {
  horaireError.value = null
  const normalizedDays = normalizeDaysInput(horaireDaysInput.value)
  if (!normalizedDays.length) {
    horaireError.value = 'Veuillez entrer au moins un jour valide (ex: lundi, mardi).'
    return
  }

  const payload = {
    ligne_bus_id: Number(horaireForm.ligne_bus_id),
    chauffeur_id: Number(horaireForm.chauffeur_id),
    departure_time: horaireForm.departure_time,
    days: normalizedDays,
  }

  try {
    if (editingHoraireId.value) {
      const response = await adminService.updateHoraire(editingHoraireId.value, payload)
      const idx = horaires.value.findIndex((h) => h.id === editingHoraireId.value)
      if (idx >= 0) {
        horaires.value[idx] = response.data as AdminHoraire
      }
    } else {
      const response = await adminService.createHoraire(payload)
      horaires.value = [response.data as AdminHoraire, ...horaires.value]
    }

    resetHoraireForm()
    await ligneStore.fetchAll()
  } catch (err: any) {
    horaireError.value = err?.response?.data?.message || 'Erreur lors de la sauvegarde de l\'horaire'
  }
}

const editHoraire = (h: AdminHoraire) => {
  editingHoraireId.value = h.id
  horaireForm.ligne_bus_id = h.ligne_bus_id
  horaireForm.chauffeur_id = h.chauffeur_id || 0
  horaireForm.departure_time = h.departure_time

  const frenchDays = Array.isArray(h.days)
    ? h.days
        .map((day) => ENGLISH_TO_FRENCH[day])
        .filter((day): day is string => Boolean(day))
        .join(', ')
    : ''

  horaireDaysInput.value = frenchDays

  document.querySelector('.inline-form')?.scrollIntoView({ behavior: 'smooth' })
}

const deleteHoraire = async (id: number) => {
  if (!confirm('Confirmer suppression de cet horaire ?')) return
  await adminService.deleteHoraire(id)
  horaires.value = horaires.value.filter((horaire) => horaire.id !== id)
  await ligneStore.fetchAll()
}

const addStopRow = () => {
  const nextOrder = ligneForm.arrets.length ? Math.max(...ligneForm.arrets.map(a => a.order)) + 1 : 1
  ligneForm.arrets.push({ name: '', latitude: '', longitude: '', order: nextOrder })
}

const removeStopRow = (index: number) => {
  ligneForm.arrets.splice(index, 1)
  // reassign order values sequentially starting at 1
  ligneForm.arrets.forEach((a, idx) => a.order = idx + 1)
}

const submitLigne = async () => {
  if (!ligneForm.name) return alert('Nom de la ligne requis')
  if (!Array.isArray(ligneForm.arrets) || ligneForm.arrets.length < 2) return alert('Veuillez ajouter au moins 2 arrêts')

  const payload = {
    name: ligneForm.name,
    description: ligneForm.description || undefined,
    color: ligneForm.color || '#00c853',
    arrets: ligneForm.arrets.map(a => ({
      name: a.name,
      latitude: Number(a.latitude),
      longitude: Number(a.longitude),
      order: Number(a.order),
    })),
  }

  await ligneStore.create(payload)
  ligneForm.name = ''
  ligneForm.description = ''
  ligneForm.color = '#00c853'
  ligneForm.arrets = []
}

const submitIncident = async () => {
  await incidentStore.create({
    ligne_bus_id: Number(incidentForm.ligne_bus_id),
    type: incidentForm.type,
    description: incidentForm.description,
  })
  incidentForm.description = ''
}

const resolveIncident = async (id: number) => {
  await incidentStore.resolve(id)
}

const deleteIncident = async (id: number) => {
  await incidentStore.delete(id)
}

const deleteLigne = async (id: number) => {
  await ligneStore.delete(id)
}

const toggleLigne = async (id: number) => {
  try {
    await apiClient.patch(`/lignes/${id}/toggle`)
    await ligneStore.fetchAll()
  } catch (err) {
    console.error('Erreur toggle ligne:', err)
  }
}

const setRole = async (membreId: number, role: string) => {
  const response = await adminService.updateRole(membreId, role)
  const idx = membres.value.findIndex((m) => m.id === membreId)
  if (idx >= 0) {
    membres.value[idx] = response.data
  }
}

const removeMembre = async (membreId: number) => {
  const ok = window.confirm('Confirmer la suppression de ce membre ?')
  if (!ok) {
    return
  }
  await adminService.deleteMembre(membreId)
  membres.value = membres.value.filter((m) => m.id !== membreId)
}

const submitChauffeur = async () => {
  chauffeurError.value = null

  try {
    const response = await adminService.createChauffeur({
      name: chauffeurForm.name,
      email: chauffeurForm.email,
      password: chauffeurForm.password,
      password_confirmation: chauffeurForm.password_confirmation,
    })

    chauffeurs.value = [response.data, ...chauffeurs.value]
    chauffeurForm.name = ''
    chauffeurForm.email = ''
    chauffeurForm.password = ''
    chauffeurForm.password_confirmation = ''
  } catch (err: any) {
    chauffeurError.value = err?.response?.data?.message || 'Erreur lors de la creation du chauffeur'
  }
}

const removeChauffeur = async (chauffeurId: number) => {
  if (!window.confirm('Confirmer la suppression de ce chauffeur ?')) return

  await adminService.deleteChauffeur(chauffeurId)
  chauffeurs.value = chauffeurs.value.filter((chauffeur) => chauffeur.id !== chauffeurId)
}

const approveDocument = async (id: number) => {
  await documentStore.approve(id)
}

const rejectDocument = async (id: number) => {
  if (!rejectReason.value) {
    alert('Veuillez entrer une raison de rejet')
    return
  }
  await documentStore.reject(id, rejectReason.value)
  rejectReason.value = ''
  rejectingDocId.value = null
}

const updateSignalementStatus = async (id: number, status: string) => {
  await signalementStore.updateStatus(id, status)
}

const deleteSignalement = async (id: number) => {
  if (!window.confirm('Confirmer la suppression de ce signalement ?')) return
  await signalementStore.remove(id)
}

const suspendMember = async (id: number) => {
  const reason = prompt('Raison de la suspension:')
  if (!reason) return
  try {
    await adminService.suspendMember(id, reason)
    const idx = membres.value.findIndex((m) => m.id === id)
    if (idx >= 0) {
      const member = membres.value[idx]
      if (member) member.is_suspended = true
    }
  } catch (err) {
    console.error('Erreur lors de la suspension:', err)
  }
}

const banMember = async (id: number) => {
  const reason = prompt('Raison du bannissement:')
  if (!reason) return
  try {
    await adminService.banMember(id, reason)
    const idx = membres.value.findIndex((m) => m.id === id)
    if (idx >= 0) {
      const member = membres.value[idx]
      if (member) member.is_banned = true
    }
  } catch (err) {
    console.error('Erreur lors du bannissement:', err)
  }
}
onMounted(async () => {
  try {
    await loadAll()
    await Promise.all([
      documentStore.fetchPending(),
      signalementStore.fetchAll(),
      statistiquesStore.fetchStats()
    ])
    if (tab.value === 'horaires') {
      await loadHoraires()
    }
  } catch (err: any) {
    membreError.value = err?.response?.data?.message || 'Erreur de chargement admin'
  }
})
</script>

<template>
  <section class="admin-page">
    <div v-if="tab === 'lignes'" class="panel">
      <form class="inline-form" @submit.prevent="submitLigne">
        <input v-model="ligneForm.name" type="text" placeholder="Nom de la ligne" required />
        <input v-model="ligneForm.description" type="text" placeholder="Description" />
        <label class="color-field">
          <span>Couleur de la ligne</span>
          <input v-model="ligneForm.color" type="color" />
        </label>
        <button type="button" class="ghost-btn" @click.prevent="addStopRow">Ajouter un arrêt</button>
        <button type="submit" class="primary-btn">Publier la ligne</button>

        <div v-if="ligneForm.arrets.length" class="stops-panel">
          <h4>Arrêts de la ligne</h4>
          <div v-for="(stop, idx) in ligneForm.arrets" :key="idx" class="stop-row">
            <input v-model="stop.name" placeholder="Nom de l'arrêt" required />
            <input v-model="stop.latitude" placeholder="Latitude" required />
            <input v-model="stop.longitude" placeholder="Longitude" required />
            <input v-model.number="stop.order" type="number" min="1" placeholder="Ordre" required />
            <button type="button" class="danger-btn small" @click="removeStopRow(idx)">x</button>
          </div>
        </div>
      </form>

      <article v-for="ligne in ligneStore.lignes" :key="ligne.id" class="row">
        <div>
          <h4 class="ligne-title"><span class="ligne-color-dot" :style="{ backgroundColor: ligne.color || '#00c853' }" />{{ ligne.name }}</h4>
          <div class="line-meta">
            <span class="muted">{{ (ligne.arrets || []).length }} arrêts</span>
            <span class="muted">&middot;</span>
            <span class="muted">{{ (ligne.arrets && ligne.arrets.length) ? `${ligne.arrets[0]?.name} → ${ligne.arrets[ligne.arrets.length-1]?.name}` : '' }}</span>
            <span class="muted">&middot;</span>
            <span class="muted">{{ ligne.next_departure ? `Prochain départ: ${ligne.next_departure}` : 'Aucun départ aujourd\'hui' }}</span>
          </div>
          <span class="status-pill" :class="ligne.is_active ? 'active' : 'cancelled'">{{ ligne.is_active ? 'active' : 'inactive' }}</span>
        </div>
        <div class="row-actions">
          <button type="button" :class="ligne.is_active ? 'danger-btn small' : 'approve-btn small'" @click="toggleLigne(ligne.id)">{{ ligne.is_active ? 'Désactiver' : 'Activer' }}</button>
          <button type="button" class="primary-btn small" @click="expandedLigne = expandedLigne === ligne.id ? null : ligne.id">Details</button>
          <button type="button" class="danger-btn small" @click="deleteLigne(ligne.id)">Supprimer</button>
        </div>
        <div v-if="expandedLigne === ligne.id" class="details stops-manager">
          <div class="line-color-editor">
            <label>Couleur actuelle</label>
            <input :value="getLigneColorDraft(ligne)" type="color" @input="ligneColorDrafts[ligne.id] = ($event.target as HTMLInputElement).value" />
            <button type="button" class="approve-btn small" @click="saveLigneColor(ligne)">Enregistrer couleur</button>
          </div>

          <h5>Arrêts actuels</h5>
          <p v-if="!getSortedArrets(ligne).length" class="muted">Aucun arrêt pour cette ligne.</p>
          <div v-for="arret in getSortedArrets(ligne)" :key="arret.id" class="stop-item">
            <template v-if="editingInlineArret[ligne.id]?.id === arret.id">
              <input v-model="editingInlineArret[ligne.id]!.name" type="text" placeholder="Nom" />
              <input v-model="editingInlineArret[ligne.id]!.latitude" type="text" placeholder="Latitude" />
              <input v-model="editingInlineArret[ligne.id]!.longitude" type="text" placeholder="Longitude" />
              <button type="button" class="primary-btn small" @click="saveInlineArret(ligne.id)">Sauvegarder</button>
              <button type="button" class="ghost-btn small" @click="cancelEditInlineArret(ligne.id)">Annuler</button>
            </template>
            <template v-else>
              <span>{{ arret.order + 1 }}. {{ arret.name }} — ({{ arret.latitude }}, {{ arret.longitude }})</span>
              <div class="row-actions">
                <button type="button" class="primary-btn small" @click="startEditInlineArret(ligne.id, arret)">✏️</button>
                <button type="button" class="danger-btn small" @click="deleteArret(arret.id)">x</button>
              </div>
            </template>
          </div>

          <form class="inline-form stop-create-form" @submit.prevent="addInlineArret(ligne.id)">
            <input v-model="ensureNewArretForm(ligne.id).name" type="text" placeholder="Nom" required />
            <input v-model="ensureNewArretForm(ligne.id).latitude" type="text" placeholder="Latitude" required />
            <input v-model="ensureNewArretForm(ligne.id).longitude" type="text" placeholder="Longitude" required />
            <button type="submit" class="approve-btn">Ajouter</button>
          </form>
        </div>
      </article>
    </div>

    <div v-if="tab === 'incidents'" class="panel">
      <form class="inline-form" @submit.prevent="submitIncident">
        <select v-model="incidentForm.ligne_bus_id" required>
          <option disabled :value="0">Selectionnez une ligne</option>
          <option v-for="ligne in ligneStore.lignes" :key="ligne.id" :value="ligne.id">{{ ligne.name }}</option>
        </select>
        <select v-model="incidentForm.type" required>
          <option value="delay">delay</option>
          <option value="breakdown">breakdown</option>
          <option value="cancelled">cancelled</option>
          <option value="other">other</option>
        </select>
        <input v-model="incidentForm.description" type="text" placeholder="Description incident" required />
        <button type="submit" class="primary-btn">Signaler incident</button>
      </form>

      <article v-for="incident in incidentStore.incidents" :key="incident.id" class="row">
        <div>
          <h4>
            <span class="status-pill pending">{{ incident.type }}</span>
            Incident #{{ incident.id }}
          </h4>
          <p>{{ incident.description }}</p>
          <small>Ligne {{ incident.ligne_bus_id }}</small>
        </div>
        <div class="row-actions">
          <button v-if="!incident.resolved_at" type="button" class="approve-btn" @click="resolveIncident(incident.id)">Resoudre</button>
          <button type="button" class="danger-btn small" @click="deleteIncident(incident.id)">Supprimer</button>
        </div>
      </article>
    </div>

    <div v-if="tab === 'chauffeurs'" class="panel">
      <form class="inline-form" @submit.prevent="submitChauffeur">
        <input v-model="chauffeurForm.name" type="text" placeholder="Nom" required />
        <input v-model="chauffeurForm.email" type="email" placeholder="Email" required />
        <input v-model="chauffeurForm.password" type="password" placeholder="Mot de passe" required />
        <input v-model="chauffeurForm.password_confirmation" type="password" placeholder="Confirmation" required />
        <button type="submit" class="primary-btn">Creer chauffeur</button>
      </form>

      <p v-if="chauffeurError" class="error">{{ chauffeurError }}</p>

      <article v-for="membre in chauffeurRows" :key="membre.id" class="row">
        <div>
          <h4>{{ membre.name }}</h4>
          <small>{{ membre.email }}</small>
          <small>{{ membre.created_at }}</small>
        </div>
        <div class="row-actions">
          <span class="status-pill active">chauffeur_bus</span>
          <button type="button" class="danger-btn small" @click="removeChauffeur(membre.id)">Supprimer</button>
        </div>
      </article>
    </div>

    <div v-if="tab === 'arrets'" class="panel">
      <form class="inline-form" @submit.prevent="submitArret">
        <input v-model="arretForm.name" type="text" placeholder="Nom de l'arret" required />
        <input v-model="arretForm.latitude" type="text" placeholder="Latitude" required />
        <input v-model="arretForm.longitude" type="text" placeholder="Longitude" required />
        <input v-model.number="arretForm.order" type="number" placeholder="Ordre" min="0" />
        <select v-model.number="arretForm.ligne_bus_id" required>
          <option disabled :value="0">Selectionnez une ligne</option>
          <option v-for="ligne in ligneStore.lignes" :key="ligne.id" :value="ligne.id">{{ ligne.name }}</option>
        </select>
        <button type="submit" class="primary-btn">{{ editingArretId ? 'Mettre a jour' : 'Nouvel arret' }}</button>
      </form>

      <article v-for="ligne in ligneStore.lignes" :key="'arrets-'+ligne.id" class="row">
        <div>
          <h4>{{ ligne.name }}</h4>
        </div>
        <ul class="details">
          <li v-for="arret in (ligne.arrets || []).slice().sort((a, b) => a.order - b.order)" :key="arret.id">
            {{ arret.order + 1 }}. {{ arret.name }} — ({{ arret.latitude }}, {{ arret.longitude }})
            <div class="row-actions">
              <button type="button" class="primary-btn small" @click="editArret(arret)">Editer</button>
              <button type="button" class="danger-btn small" @click="deleteArret(arret.id)">Supprimer</button>
            </div>
          </li>
        </ul>
      </article>
    </div>

    <div v-if="tab === 'horaires'" class="panel">
      <form class="inline-form" @submit.prevent="submitHoraire">
        <select v-model.number="horaireForm.ligne_bus_id" required>
          <option disabled :value="0">Selectionnez une ligne</option>
          <option v-for="ligne in ligneStore.lignes" :key="ligne.id" :value="ligne.id">{{ ligne.name }}</option>
        </select>
        <select v-model.number="horaireForm.chauffeur_id" required>
          <option disabled :value="0">Selectionnez un chauffeur</option>
          <option v-for="m in chauffeurRows" :key="m.id" :value="m.id">{{ m.name }}</option>
        </select>
        <input v-model="horaireForm.departure_time" type="time" required />
        <input v-model="horaireDaysInput" type="text" placeholder="Jours (ex: lundi, mardi)" required />
        <button type="submit" class="primary-btn">{{ editingHoraireId ? 'Mettre a jour' : 'Nouvel horaire' }}</button>
      </form>

      <p v-if="horaireError" class="error">{{ horaireError }}</p>
      <p v-if="!chauffeurRows.length" class="info">Aucun chauffeur disponible.</p>

      <p v-if="!horaires.length" class="info">Aucun horaire.</p>

      <article v-for="h in horaires" :key="'horaire-'+h.id" class="row">
        <div>
          <h4>{{ h.ligne?.name || `Ligne #${h.ligne_bus_id}` }}</h4>
          <p>Chauffeur: {{ h.chauffeur?.name || (h.chauffeur_id ?? 'N/A') }}</p>
          <small>Jour(s): {{ (h.days || []).join(', ') }}</small>
          <br />
          <small>Heure depart: {{ h.departure_time }}</small>
          <br />
          <small>Heure arrivee: N/A</small>
        </div>
        <div class="row-actions">
          <button type="button" class="primary-btn small" @click="editHoraire(h)">Editer</button>
          <button type="button" class="danger-btn small" @click="deleteHoraire(h.id)">Supprimer</button>
        </div>
      </article>
    </div>

    <div v-if="tab === 'documents'" class="panel">
      <h3>Documents en attente</h3>
      <p v-if="documentStore.pending.length === 0" class="info">Aucun document en attente</p>
      <article v-for="doc in documentStore.pending" :key="doc.id" class="row">
        <div>
          <h4>Document #{{ doc.id }}</h4>
          <p>{{ doc.type }}</p>
          <small>Utilisateur: {{ doc.membre?.name || 'N/A' }}</small>
        </div>
        <div class="row-actions">
          <button type="button" class="approve-btn" @click="approveDocument(doc.id)">Approuver</button>
          <button
            v-if="rejectingDocId !== doc.id"
            type="button"
            class="danger-btn small"
            @click="rejectingDocId = doc.id"
          >
            Rejeter
          </button>
          <div v-if="rejectingDocId === doc.id" class="reject-form">
            <input
              v-model="rejectReason"
              type="text"
              placeholder="Raison du rejet"
              @keydown.escape="rejectingDocId = null; rejectReason = ''"
            />
            <button type="button" class="danger-btn small" @click="rejectDocument(doc.id)">Confirmer</button>
            <button type="button" @click="rejectingDocId = null; rejectReason = ''" class="ghost-btn small">Annuler</button>
          </div>
        </div>
      </article>
    </div>

    <div v-if="tab === 'signalements'" class="panel">
      <h3>Signalements</h3>
      <p v-if="signalementStore.list.length === 0" class="info">Aucun signalement</p>
      <article v-for="sig in signalementStore.list" :key="sig.id" class="row">
        <div>
          <h4>Signalement #{{ sig.id }}</h4>
          <p><strong>Conducteur:</strong> {{ sig.conducteur?.name || sig.reported?.name || 'Conducteur inconnu' }}</p>
          <p><strong>Membre:</strong> {{ sig.membre?.name || sig.reporter?.name || 'Membre inconnu' }}</p>
          <p v-if="sig.trajet">
            <strong>Trajet:</strong>
            {{ sig.trajet.departure_point }} → {{ sig.trajet.arrival_point }}
          </p>
          <p><strong>Raison:</strong> {{ sig.reason }}</p>
          <p v-if="sig.description"><strong>Description:</strong> {{ sig.description }}</p>
          <small>Date: {{ formatSignalementDate(sig.created_at) }}</small>
          <br />
          <small>Status: <strong>{{ sig.status }}</strong></small>
        </div>
        <div class="row-actions">
          <select :value="sig.status" @change="updateSignalementStatus(sig.id, ($event.target as HTMLSelectElement).value)">
            <option value="en_attente">En attente</option>
            <option value="traite">Traité</option>
            <option value="archive">Archivé</option>
          </select>
          <button type="button" class="danger-btn small" @click="deleteSignalement(sig.id)">Supprimer</button>
        </div>
      </article>
    </div>

    <div v-if="tab === 'membres'" class="panel">
      <h3>Membres</h3>
      <p v-if="membreError" class="error">{{ membreError }}</p>
      <p v-if="membres.length === 0" class="info">Aucun membre</p>
      <article v-for="membre in membres" :key="membre.id" class="row">
        <div>
          <h4>{{ membre.name }}</h4>
          <small>{{ membre.email }}</small>
          <br />
          <small v-if="membre.is_suspended" class="status-pill cancelled">Suspendu</small>
          <small v-if="membre.is_banned" class="status-pill banned">Banni</small>
        </div>
        <div class="row-actions">
          <button
            v-if="!membre.is_suspended"
            type="button"
            class="warning-btn"
            @click="suspendMember(membre.id)"
          >
            Suspendre
          </button>
          <button
            v-if="!membre.is_banned"
            type="button"
            class="danger-btn small"
            @click="banMember(membre.id)"
          >
            Bannir
          </button>
        </div>
      </article>
    </div>

    <div v-if="tab === 'statistiques'" class="stats-dashboard">
      <div v-if="stats" class="stats-stack">
        <header class="stats-hero">
          <div>
            <p class="stats-eyebrow">Vue d’ensemble</p>
            <h3>Statistiques de la plateforme</h3>
            <p class="stats-subtitle">
              Même source API, mais une lecture plus rapide avec indicateurs, graphiques et hiérarchie visuelle.
            </p>
          </div>
          <div class="stats-meta">
            <span class="meta-chip">API /admin/statistiques</span>
            <span class="meta-chip meta-chip--accent">{{ formatCount(stats.membres?.total ?? 0) }} utilisateurs</span>
          </div>
        </header>

        <section class="stats-kpi-grid" aria-label="Indicateurs clés">
          <article
            v-for="card in statsCards"
            :key="card.key"
            class="kpi-card"
            :style="{ backgroundImage: card.gradient }"
          >
            <div class="kpi-card__top">
              <div class="kpi-icon" :style="{ color: card.accent, borderColor: `${card.accent}55` }">
                {{ card.icon }}
              </div>
              <span class="trend-pill" :class="`trend-pill--${card.trend}`">
                <span>{{ trendGlyph(card.trend) }}</span>
                {{ card.trendLabel }}
              </span>
            </div>
            <div class="kpi-value">{{ formatCount(card.value) }}</div>
            <div class="kpi-label">{{ card.label }}</div>
            <div class="kpi-detail">{{ card.detail }}</div>
          </article>
        </section>

        <section class="stats-charts-grid">
          <article class="chart-panel">
            <div class="chart-panel__header">
              <div>
                <p class="chart-kicker">Utilisateurs par rôle</p>
                <h4>Répartition des comptes</h4>
              </div>
              <span class="chart-badge">{{ formatCount(stats.membres?.total ?? 0) }} comptes</span>
            </div>

            <div class="chart-panel__body chart-panel__body--split">
              <svg class="bar-chart" viewBox="0 0 560 320" role="img" aria-label="Utilisateurs par rôle">
                <g v-for="(tick, tickIndex) in roleChartTicks" :key="`role-tick-${tickIndex}`">
                  <line x1="56" x2="520" :y1="50 + tickIndex * 60" :y2="50 + tickIndex * 60" class="grid-line" />
                  <text x="18" :y="54 + tickIndex * 60" class="axis-label">{{ formatCount(tick) }}</text>
                </g>
                <g v-for="(bar, index) in userRoleBars" :key="bar.key">
                  <rect
                    :x="72 + (index * roleSlotWidth) + (roleSlotWidth * 0.14)"
                    :y="228 - ((bar.value / roleChartMax) * 168)"
                    :width="Math.max(roleSlotWidth * 0.58, 36)"
                    :height="Math.max((bar.value / roleChartMax) * 168, bar.value ? 14 : 8)"
                    :fill="bar.color"
                    rx="16"
                  />
                  <text
                    :x="72 + (index * roleSlotWidth) + (roleSlotWidth * 0.43)"
                    :y="214 - ((bar.value / roleChartMax) * 168)"
                    text-anchor="middle"
                    class="bar-value"
                  >
                    {{ formatCount(bar.value) }}
                  </text>
                  <text
                    :x="72 + (index * roleSlotWidth) + (roleSlotWidth * 0.43)"
                    y="264"
                    text-anchor="middle"
                    class="bar-label"
                  >
                    {{ bar.label }}
                  </text>
                </g>
              </svg>

              <div class="chart-legend">
                <div v-for="bar in userRoleBars" :key="bar.key" class="legend-item">
                  <span class="legend-swatch" :style="{ background: bar.color }"></span>
                  <div>
                    <strong>{{ bar.label }}</strong>
                    <span>{{ formatCount(bar.value) }} comptes</span>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <article class="chart-panel">
            <div class="chart-panel__header">
              <div>
                <p class="chart-kicker">Réservations par statut</p>
                <h4>Répartition des demandes</h4>
              </div>
              <span class="chart-badge">{{ formatCount(reservationTotal) }} total</span>
            </div>

            <div class="chart-panel__body chart-panel__body--split">
              <div class="donut-chart-wrap">
                <div class="donut-chart" :style="{ background: reservationGradient }">
                  <div class="donut-chart__inner">
                    <strong>{{ formatCount(reservationTotal) }}</strong>
                    <span>réservations</span>
                  </div>
                </div>
              </div>

              <div class="chart-legend">
                <div v-for="segment in reservationLegend" :key="segment.key" class="legend-item">
                  <span class="legend-swatch" :style="{ background: segment.color }"></span>
                  <div>
                    <strong>{{ segment.label }}</strong>
                    <span>{{ formatCount(segment.value) }} • {{ formatPercent(segment.percent) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </section>

        <article class="chart-panel chart-panel--wide">
          <div class="chart-panel__header">
            <div>
              <p class="chart-kicker">Activité récente</p>
              <h4>Lecture comparative des 6 KPI</h4>
            </div>
            <span class="chart-badge">Série synthétique</span>
          </div>

          <div class="chart-panel__body">
              <svg class="bar-chart bar-chart--wide" viewBox="0 0 820 340" role="img" aria-label="Activité récente">
              <g v-for="(tick, tickIndex) in activityChartTicks" :key="`activity-tick-${tickIndex}`">
                <line x1="46" x2="790" :y1="activityChartTop + tickIndex * activityChartStep" :y2="activityChartTop + tickIndex * activityChartStep" class="grid-line" />
                <text x="12" :y="activityChartTop + 4 + tickIndex * activityChartStep" class="axis-label">{{ formatCount(tick) }}</text>
              </g>

              <line x1="46" x2="790" :y1="activityChartBaseline" :y2="activityChartBaseline" class="grid-line grid-line--baseline" />

              <g v-for="(bar, index) in activityBars" :key="bar.key">
                <rect
                  :x="52 + (index * activitySlotWidth) + (activitySlotWidth * 0.12)"
                  :y="activityChartBaseline - ((bar.value / activityChartMax) * activityChartHeight)"
                  :width="Math.max(activitySlotWidth * 0.58, 44)"
                  :height="Math.max((bar.value / activityChartMax) * activityChartHeight, bar.value ? 14 : 8)"
                  :fill="bar.color"
                  rx="18"
                />
                <text
                  :x="52 + (index * activitySlotWidth) + (activitySlotWidth * 0.41)"
                  :y="activityChartBaseline - ((bar.value / activityChartMax) * activityChartHeight) - 12"
                  text-anchor="middle"
                  class="bar-value"
                >
                  {{ formatCount(bar.value) }}
                </text>
                <text
                  :x="52 + (index * activitySlotWidth) + (activitySlotWidth * 0.41)"
                  :y="index >= 3 ? activityChartBaseline + 24 : activityChartBaseline + 34"
                  text-anchor="middle"
                  class="bar-label"
                >
                  {{ bar.shortLabel }}
                </text>
              </g>
            </svg>
          </div>
        </article>
      </div>

      <p v-else class="info">Chargement des statistiques...</p>
    </div>
  </section>
</template>

<style scoped>
.admin-page {
  min-height: 100vh;
  padding: 40px 24px;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.page-title {
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px;
}

.page-subtitle {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0;
}

.tab-nav {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 32px;
}

.tab-btn,
.primary-btn,
.approve-btn,
.danger-btn,
.warning-btn,
.ghost-btn,
.row-actions select,
.inline-form input,
.inline-form select,
.reject-form input {
  font-family: inherit;
}

.tab-btn {
  background: rgba(253, 249, 240, 0.06);
  border: 1px solid rgba(253, 249, 240, 0.12);
  color: rgba(253, 249, 240, 0.6);
  border-radius: 999px;
  padding: 8px 20px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover:not(.active) {
  border-color: rgba(255, 225, 128, 0.4);
  color: #fdf9f0;
}

.tab-btn.active {
  background: #ffe180;
  border-color: #ffe180;
  color: #1b3d2f;
  font-weight: 700;
}

.panel {
  margin-top: 1rem;
  display: grid;
  gap: 0.75rem;
  background: rgba(253, 249, 240, 0.06);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.15);
  border-radius: 20px;
  padding: 24px;
  margin-bottom: 20px;
}

.inline-form {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.45rem;
}

.inline-form input,
.inline-form select,
.inline-form button,
.row-actions button,
.row-actions select {
  border-radius: 12px;
}

.inline-form input,
.inline-form select,
.reject-form input,
.row-actions select {
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  color: #fdf9f0;
  color-scheme: dark;
  padding: 10px 14px;
  width: 100%;
}

.inline-form select option,
.row-actions select option {
  background: #1b3d2f;
  color: #fdf9f0;
}

.inline-form input::placeholder,
.reject-form input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.inline-form input:focus,
.inline-form select:focus,
.reject-form input:focus,
.row-actions select:focus {
  outline: none;
  border-color: #ffe180;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 999px;
  padding: 10px 24px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.primary-btn.small {
  padding: 8px 16px;
  font-size: 13px;
}

.approve-btn,
.warning-btn,
.danger-btn,
.ghost-btn {
  border-radius: 999px;
  padding: 8px 16px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.approve-btn {
  background: rgba(100, 200, 120, 0.15);
  border: 1px solid rgba(100, 200, 120, 0.3);
  color: #6ec47a;
}

.approve-btn:hover {
  background: rgba(100, 200, 120, 0.3);
}

.warning-btn {
  background: rgba(255, 193, 7, 0.15);
  border: 1px solid rgba(255, 193, 7, 0.3);
  color: #ffc107;
}

.danger-btn {
  background: rgba(255, 107, 107, 0.15);
  border: 1px solid rgba(255, 107, 107, 0.4);
  color: #ff6b6b;
}

.danger-btn:hover {
  background: rgba(255, 107, 107, 0.3);
}

.ghost-btn {
  background: transparent;
  border: 1px solid rgba(253, 249, 240, 0.15);
  color: #fdf9f0;
}

.row {
  border: 1px solid rgba(255, 225, 128, 0.15);
  border-radius: 16px;
  background: rgba(253, 249, 240, 0.06);
  padding: 24px;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: start;
  flex-wrap: wrap;
  transition: border-color 0.2s ease, background 0.2s ease;
}

.row:hover {
  border-color: rgba(255, 225, 128, 0.5);
  background: rgba(253, 249, 240, 0.08);
}

.row-actions {
  display: flex;
  gap: 0.45rem;
  flex-wrap: wrap;
}

.details {
  width: 100%;
  margin: 0;
  padding-left: 1rem;
}

.ligne-title {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.ligne-color-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.35);
}

.color-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  padding: 10px 14px;
  background: rgba(253, 249, 240, 0.07);
}

.color-field input[type='color'] {
  width: 44px;
  height: 32px;
  padding: 0;
  border: none;
  background: transparent;
}

.stops-manager {
  display: grid;
  gap: 0.65rem;
}

.line-color-editor {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.65rem;
  border: 1px solid rgba(253, 249, 240, 0.1);
  border-radius: 12px;
  padding: 10px 12px;
}

.line-color-editor input[type='color'] {
  width: 42px;
  height: 30px;
  padding: 0;
  border: none;
  background: transparent;
}

.stop-item {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  border: 1px solid rgba(253, 249, 240, 0.1);
  border-radius: 12px;
  padding: 10px 12px;
}

.stop-item input {
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  color: #fdf9f0;
  border-radius: 10px;
  padding: 8px 10px;
}

.stop-create-form {
  margin-top: 0.5rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid transparent;
}

.status-pill.active {
  background: rgba(100, 200, 120, 0.15);
  color: #6ec47a;
  border-color: rgba(100, 200, 120, 0.3);
}

.status-pill.pending {
  background: rgba(255, 225, 128, 0.15);
  color: #ffe180;
  border-color: rgba(255, 225, 128, 0.3);
}

.status-pill.cancelled {
  background: rgba(255, 107, 107, 0.15);
  color: #ff6b6b;
  border-color: rgba(255, 107, 107, 0.3);
}

.status-pill.banned {
  background: rgba(180, 0, 0, 0.2);
  color: #ff4444;
  border-color: rgba(180, 0, 0, 0.3);
}

.error {
  color: #c21f34;
}

.info {
  color: rgba(253, 249, 240, 0.4);
  font-style: normal;
  text-align: center;
  padding: 60px 0;
  font-size: 15px;
}

.reject-form {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  width: 100%;
}

.reject-form input {
  border-radius: 12px;
  flex: 1;
  min-width: 150px;
}

.reject-form button {
  border-radius: 999px;
  padding: 8px 16px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-card {
  border: 1px solid rgba(255, 225, 128, 0.15);
  border-radius: 20px;
  background: rgba(253, 249, 240, 0.06);
  padding: 1.5rem;
  text-align: center;
  backdrop-filter: blur(20px) saturate(180%);
}

.stat-icon {
  font-size: 24px;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 36px;
  font-weight: 700;
  color: #ffe180;
}

.stat-label {
  font-size: 13px;
  color: rgba(253, 249, 240, 0.6);
  margin-top: 0.25rem;
}

.empty-state {
  color: rgba(253, 249, 240, 0.4);
  text-align: center;
  padding: 60px 0;
  font-size: 15px;
}

.stats-dashboard {
  display: grid;
  gap: 24px;
}

.stats-stack {
  display: grid;
  gap: 24px;
}

.stats-hero {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  padding: 22px 24px;
  background: var(--card, rgba(253, 249, 240, 0.08));
  border: 1px solid var(--border, rgba(253, 249, 240, 0.15));
  border-radius: 24px;
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
}

.stats-eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 11px;
  color: rgba(253, 249, 240, 0.58);
}

.stats-hero h3 {
  margin: 0;
  color: #fdf9f0;
  font-size: clamp(1.4rem, 1.2vw + 1rem, 2rem);
}

.stats-subtitle {
  margin: 8px 0 0;
  color: rgba(253, 249, 240, 0.66);
  line-height: 1.55;
  max-width: 58ch;
}

.stats-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 999px;
  border: 1px solid rgba(253, 249, 240, 0.15);
  background: rgba(253, 249, 240, 0.06);
  color: rgba(253, 249, 240, 0.82);
  font-size: 13px;
}

.meta-chip--accent {
  background: rgba(255, 225, 128, 0.14);
  border-color: rgba(255, 225, 128, 0.3);
  color: #ffe180;
}

.stats-kpi-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.kpi-card {
  position: relative;
  overflow: hidden;
  min-height: 198px;
  padding: 20px;
  border-radius: 24px;
  border: 1px solid rgba(253, 249, 240, 0.14);
  background: var(--card, rgba(253, 249, 240, 0.08));
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
  color: #fdf9f0;
}

.kpi-card::after {
  content: '';
  position: absolute;
  inset: auto -22% -38% auto;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 225, 128, 0.22), transparent 68%);
  filter: blur(4px);
  pointer-events: none;
}

.kpi-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.kpi-icon {
  width: 46px;
  height: 46px;
  border-radius: 16px;
  border: 1px solid rgba(253, 249, 240, 0.14);
  background: rgba(253, 249, 240, 0.06);
  display: grid;
  place-items: center;
  font-size: 20px;
  flex: none;
}

.trend-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 10px;
  border-radius: 999px;
  border: 1px solid rgba(253, 249, 240, 0.12);
  background: rgba(253, 249, 240, 0.06);
  color: rgba(253, 249, 240, 0.78);
  font-size: 12px;
  white-space: nowrap;
}

.trend-pill--up {
  border-color: rgba(255, 225, 128, 0.3);
  color: #ffe180;
}

.trend-pill--down {
  border-color: rgba(255, 158, 122, 0.28);
  color: #ffcfbb;
}

.trend-pill--flat {
  color: rgba(253, 249, 240, 0.72);
}

.kpi-value {
  margin-top: 18px;
  font-size: clamp(2.2rem, 3.2vw, 3.4rem);
  line-height: 0.95;
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff1a6;
}

.kpi-label {
  margin-top: 12px;
  font-size: 14px;
  font-weight: 700;
  color: #fdf9f0;
}

.kpi-detail {
  margin-top: 8px;
  font-size: 13px;
  line-height: 1.5;
  color: rgba(253, 249, 240, 0.68);
}

.stats-charts-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.chart-panel {
  display: grid;
  gap: 18px;
  padding: 22px;
  border-radius: 24px;
  border: 1px solid var(--border, rgba(253, 249, 240, 0.15));
  background: var(--card, rgba(253, 249, 240, 0.08));
  backdrop-filter: blur(24px) saturate(180%);
  -webkit-backdrop-filter: blur(24px) saturate(180%);
}

.chart-panel--wide {
  margin-top: 4px;
}

.chart-panel__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.chart-kicker {
  margin: 0 0 4px;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: rgba(253, 249, 240, 0.55);
}

.chart-panel__header h4 {
  margin: 0;
  color: #fdf9f0;
  font-size: 18px;
}

.chart-badge {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(255, 225, 128, 0.12);
  border: 1px solid rgba(255, 225, 128, 0.22);
  color: #ffe180;
  font-size: 12px;
}

.chart-panel__body {
  display: grid;
  gap: 18px;
}

.chart-panel__body--split {
  grid-template-columns: minmax(0, 1.12fr) minmax(220px, 0.88fr);
  align-items: center;
}

.bar-chart {
  width: 100%;
  height: auto;
  overflow: visible;
}

.grid-line {
  stroke: rgba(253, 249, 240, 0.1);
  stroke-width: 1;
}

.axis-label {
  fill: rgba(253, 249, 240, 0.46);
  font-size: 12px;
}

.bar-value {
  fill: #ffe180;
  font-size: 12px;
  font-weight: 700;
}

.bar-label {
  fill: rgba(253, 249, 240, 0.7);
  font-size: 12px;
}

.chart-legend {
  display: grid;
  gap: 12px;
}

.legend-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 18px;
  background: rgba(253, 249, 240, 0.05);
  border: 1px solid rgba(253, 249, 240, 0.1);
}

.legend-swatch {
  width: 12px;
  height: 12px;
  margin-top: 4px;
  border-radius: 999px;
  box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.04);
  flex: none;
}

.legend-item strong {
  display: block;
  margin-bottom: 3px;
  color: #fdf9f0;
  font-size: 14px;
}

.legend-item span {
  display: block;
  color: rgba(253, 249, 240, 0.66);
  font-size: 12px;
}

.donut-chart-wrap {
  display: grid;
  place-items: center;
  min-height: 260px;
}

.donut-chart {
  width: 210px;
  height: 210px;
  border-radius: 50%;
  padding: 18px;
  box-shadow: inset 0 0 0 1px rgba(253, 249, 240, 0.08), 0 18px 28px rgba(0, 0, 0, 0.18);
  display: grid;
  place-items: center;
}

.donut-chart__inner {
  width: 124px;
  height: 124px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  text-align: center;
  background: rgba(27, 61, 47, 0.96);
  border: 1px solid rgba(253, 249, 240, 0.12);
  color: #fdf9f0;
  line-height: 1.15;
}

.donut-chart__inner strong {
  display: block;
  font-size: 1.95rem;
  color: #ffe180;
}

.donut-chart__inner span {
  display: block;
  margin-top: 4px;
  font-size: 12px;
  color: rgba(253, 249, 240, 0.68);
}

@media (min-width: 980px) {
  .inline-form {
    grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
  }
}

@media (max-width: 1024px) {
  .stats-kpi-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .stats-charts-grid {
    grid-template-columns: 1fr;
  }

  .chart-panel__body--split {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .admin-page {
    padding: 40px 16px;
  }

  .stats-hero {
    flex-direction: column;
    align-items: flex-start;
  }

  .stats-kpi-grid {
    grid-template-columns: 1fr;
  }

  .chart-panel {
    padding: 18px;
  }

  .donut-chart {
    width: 180px;
    height: 180px;
  }

  .donut-chart__inner {
    width: 108px;
    height: 108px;
  }

  .row {
    padding: 20px;
  }
}
</style>

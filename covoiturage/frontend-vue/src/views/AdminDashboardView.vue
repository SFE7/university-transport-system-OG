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
import horaireService from '@/services/horaireService'
import type { Membre } from '@/types'

const route = useRoute()
const router = useRouter()
const ligneStore = useLigneStore()
const incidentStore = useIncidentStore()
const documentStore = useDocumentStore()
const signalementStore = useSignalementStore()
const statistiquesStore = useStatistiquesStore()

const tab = ref<'lignes' | 'incidents' | 'chauffeurs' | 'arrets' | 'horaires' | 'documents' | 'signalements' | 'membres' | 'statistiques'>('lignes')
const expandedLigne = ref<number | null>(null)

const ligneForm = reactive({ name: '', description: '' })
const incidentForm = reactive({ ligne_bus_id: 0, type: 'other', description: '' })
const arretForm = reactive({ name: '', latitude: '', longitude: '', order: 0, ligne_bus_id: 0 })
const editingArretId = ref<number | null>(null)

const horaireForm = reactive({ ligne_bus_id: 0, chauffeur_id: 0, departure_time: '', days: [] as string[] })
const editingHoraireId = ref<number | null>(null)

const membres = ref<Membre[]>([])
const membreError = ref<string | null>(null)

watch(membres, (val) => console.log('membres changed:', val), { immediate: true })

const rejectReason = ref('')
const rejectingDocId = ref<number | null>(null)

const chauffeurRows = computed(() =>
  membres.value.filter((m) => {
    const role = m.role as string
    return role === 'chauffeur_bus'
  })
)

const loadAll = async () => {
  try {
    console.log('loadAll called')
    await Promise.all([ligneStore.fetchAll(), incidentStore.fetchAll()])
    const membResp = await adminService.getMembres()
    console.log('membResp raw:', membResp)
    membres.value = membResp.data
    console.log('membres.value after assign:', membres.value)
  } catch (err) {
    console.error('Error loading admin data:', err)
    throw err
  }
}

// Watch route query param for tab
watch(() => route.query.tab, (newTab) => {
  const validTabs = ['lignes', 'incidents', 'chauffeurs', 'arrets', 'horaires', 'documents', 'signalements', 'membres', 'statistiques']
  if (newTab && validTabs.includes(newTab as string)) {
    tab.value = newTab as any
  }
}, { immediate: true })

// Watch tab changes to update route
watch(tab, (newTab) => {
  router.push({ query: { tab: newTab } })
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

const submitHoraire = async () => {
  const payload = {
    ligne_bus_id: Number(horaireForm.ligne_bus_id),
    chauffeur_id: Number(horaireForm.chauffeur_id),
    departure_time: horaireForm.departure_time,
    days: horaireForm.days,
  }
  if (editingHoraireId.value) {
    await horaireService.update(editingHoraireId.value, payload)
    editingHoraireId.value = null
  } else {
    await horaireService.create(payload)
  }
  horaireForm.ligne_bus_id = 0
  horaireForm.chauffeur_id = 0
  horaireForm.departure_time = ''
  horaireForm.days = []
  await ligneStore.fetchAll()
}

const editHoraire = (h: any) => {
  editingHoraireId.value = h.id
  horaireForm.ligne_bus_id = h.ligne_bus_id
  horaireForm.chauffeur_id = h.chauffeur_id
  horaireForm.departure_time = h.departure_time
  horaireForm.days = Array.isArray(h.days) ? h.days : []
}

const deleteHoraire = async (id: number) => {
  if (!confirm('Confirmer suppression de cet horaire ?')) return
  await horaireService.delete(id)
  await ligneStore.fetchAll()
}

const submitLigne = async () => {
  await ligneStore.create({ name: ligneForm.name, description: ligneForm.description || undefined })
  ligneForm.name = ''
  ligneForm.description = ''
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
        <button type="submit" class="primary-btn">Nouvelle ligne</button>
      </form>

      <article v-for="ligne in ligneStore.lignes" :key="ligne.id" class="row">
        <div>
          <h4>{{ ligne.name }}</h4>
          <span class="status-pill" :class="ligne.is_active ? 'active' : 'cancelled'">{{ ligne.is_active ? 'active' : 'inactive' }}</span>
        </div>
        <div class="row-actions">
          <button type="button" class="primary-btn small" @click="expandedLigne = expandedLigne === ligne.id ? null : ligne.id">Details</button>
          <button type="button" class="danger-btn small" @click="deleteLigne(ligne.id)">Supprimer</button>
        </div>
        <ul v-if="expandedLigne === ligne.id" class="details">
          <li v-for="arret in (ligne.arrets || []).slice().sort((a, b) => a.order - b.order)" :key="arret.id">
            {{ arret.order + 1 }}. {{ arret.name }}
          </li>
        </ul>
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
      <p v-if="membreError" class="error">{{ membreError }}</p>

      <article v-for="membre in chauffeurRows" :key="membre.id" class="row">
        <div>
          <h4>{{ membre.name }}</h4>
          <small>{{ membre.email }}</small>
        </div>
        <div class="row-actions">
          <select :value="membre.role" @change="setRole(membre.id, ($event.target as HTMLSelectElement).value)">
            <option value="membre">membre</option>
            <option value="conducteur">conducteur</option>
            <option value="chauffeur_bus">chauffeur_bus</option>
            <option value="admin">admin</option>
          </select>
          <button type="button" class="danger-btn small" @click="removeMembre(membre.id)">Supprimer</button>
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
        <input v-model="horaireForm.days" type="text" placeholder="jours (comma-separated)" @change="horaireForm.days = (horaireForm.days as unknown as string).split(',').map(s=>s.trim())" />
        <button type="submit" class="primary-btn">{{ editingHoraireId ? 'Mettre a jour' : 'Nouvel horaire' }}</button>
      </form>

      <article v-for="ligne in ligneStore.lignes" :key="'horaire-'+ligne.id" class="row">
        <div>
          <h4>{{ ligne.name }}</h4>
        </div>
        <ul class="details">
          <li v-for="h in (ligne.horaires || [])" :key="h.id">
            {{ h.departure_time }} — {{ (h.days || []).join(', ') }} — Chauffeur: {{ h.chauffeur?.name || (h.chauffeur_id ?? 'N/A') }}
            <div class="row-actions">
              <button type="button" class="primary-btn small" @click="editHoraire(h)">Editer</button>
              <button type="button" class="danger-btn small" @click="deleteHoraire(h.id)">Supprimer</button>
            </div>
          </li>
        </ul>
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
          <p>{{ sig.reason }}</p>
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

    <div v-if="tab === 'statistiques'" class="panel">
      <h3>Statistiques</h3>
      <div v-if="statistiquesStore.stats" class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-value">{{ statistiquesStore.stats.membres?.total || 0 }}</div>
          <div class="stat-label">Utilisateurs</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🛣️</div>
          <div class="stat-value">{{ statistiquesStore.stats.trajets?.total || 0 }}</div>
          <div class="stat-label">Trajets</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🎫</div>
          <div class="stat-value">{{ statistiquesStore.stats.reservations?.total || 0 }}</div>
          <div class="stat-label">Réservations</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🚌</div>
          <div class="stat-value">{{ statistiquesStore.stats.bus?.lignes || 0 }}</div>
          <div class="stat-label">Lignes Bus</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⚠️</div>
          <div class="stat-value">{{ statistiquesStore.stats.bus?.incidents || 0 }}</div>
          <div class="stat-label">Incidents</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📄</div>
          <div class="stat-value">{{ statistiquesStore.stats.documents?.en_attente || 0 }}</div>
          <div class="stat-label">Documents en attente</div>
        </div>
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
  padding: 10px 14px;
  width: 100%;
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

@media (min-width: 980px) {
  .inline-form {
    grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
  }
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .admin-page {
    padding: 40px 16px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .row {
    padding: 20px;
  }
}
</style>

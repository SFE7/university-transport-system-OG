<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useLigneStore } from '@/stores/ligneStore'
import { useIncidentStore } from '@/stores/incidentStore'
import adminService from '@/services/adminService'
import type { Membre } from '@/types'

const ligneStore = useLigneStore()
const incidentStore = useIncidentStore()

const tab = ref<'lignes' | 'incidents' | 'chauffeurs'>('lignes')
const expandedLigne = ref<number | null>(null)

const ligneForm = reactive({ name: '', description: '' })
const incidentForm = reactive({ ligne_bus_id: 0, type: 'other', description: '' })

const membres = ref<Membre[]>([])
const membreError = ref<string | null>(null)

const chauffeurRows = computed(() =>
  membres.value.filter((m) => {
    const role = m.role as string
    return role === 'chauffeur_bus'
  })
)

const loadAll = async () => {
  await Promise.all([ligneStore.fetchAll(), incidentStore.fetchAll()])
  const membResp = await adminService.getMembres()
  membres.value = membResp.data
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

onMounted(async () => {
  try {
    await loadAll()
  } catch (err: any) {
    membreError.value = err?.response?.data?.message || 'Erreur de chargement admin'
  }
})
</script>

<template>
  <section class="admin-page">
    <header>
      <h1>Dashboard Admin</h1>
      <p>Pilotage des lignes, incidents et chauffeurs.</p>
    </header>

    <div class="tabs">
      <button :class="{ active: tab === 'lignes' }" @click="tab = 'lignes'">Lignes</button>
      <button :class="{ active: tab === 'incidents' }" @click="tab = 'incidents'">Incidents</button>
      <button :class="{ active: tab === 'chauffeurs' }" @click="tab = 'chauffeurs'">Chauffeurs</button>
    </div>

    <div v-if="tab === 'lignes'" class="panel">
      <form class="inline-form" @submit.prevent="submitLigne">
        <input v-model="ligneForm.name" type="text" placeholder="Nom de la ligne" required />
        <input v-model="ligneForm.description" type="text" placeholder="Description" />
        <button type="submit">Nouvelle ligne</button>
      </form>

      <article v-for="ligne in ligneStore.lignes" :key="ligne.id" class="row">
        <div>
          <h4>{{ ligne.name }}</h4>
          <span class="badge" :class="ligne.is_active ? 'ok' : 'ko'">{{ ligne.is_active ? 'active' : 'inactive' }}</span>
        </div>
        <div class="row-actions">
          <button @click="expandedLigne = expandedLigne === ligne.id ? null : ligne.id">Details</button>
          <button class="danger" @click="deleteLigne(ligne.id)">Supprimer</button>
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
        <button type="submit">Signaler incident</button>
      </form>

      <article v-for="incident in incidentStore.incidents" :key="incident.id" class="row">
        <div>
          <h4>
            <span class="badge type">{{ incident.type }}</span>
            Incident #{{ incident.id }}
          </h4>
          <p>{{ incident.description }}</p>
          <small>Ligne {{ incident.ligne_bus_id }}</small>
        </div>
        <div class="row-actions">
          <button v-if="!incident.resolved_at" @click="resolveIncident(incident.id)">Resoudre</button>
          <button class="danger" @click="deleteIncident(incident.id)">Supprimer</button>
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
          <button class="danger" @click="removeMembre(membre.id)">Supprimer</button>
        </div>
      </article>
    </div>
  </section>
</template>

<style scoped>
.admin-page {
  min-height: 100vh;
  padding: 1rem;
  background: linear-gradient(130deg, #eef3ff, #fff0f6 46%, #fffdf8);
}

.tabs {
  margin-top: 1rem;
  display: flex;
  gap: 0.5rem;
}

.tabs button {
  border: 1px solid #cad2df;
  background: #fff;
  border-radius: 10px;
  padding: 0.4rem 0.8rem;
  cursor: pointer;
}

.tabs button.active {
  background: #2f4a7d;
  color: #fff;
  border-color: #2f4a7d;
}

.panel {
  margin-top: 1rem;
  display: grid;
  gap: 0.75rem;
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
  border: 1px solid #d3dbe8;
  border-radius: 10px;
  padding: 0.5rem;
}

.inline-form button {
  background: #154f88;
  color: #fff;
}

.row {
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #fff;
  padding: 0.75rem;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: start;
  flex-wrap: wrap;
}

.row-actions {
  display: flex;
  gap: 0.45rem;
  flex-wrap: wrap;
}

.badge {
  display: inline-block;
  border-radius: 999px;
  padding: 0.2rem 0.55rem;
  font-size: 0.75rem;
}

.badge.ok {
  background: #d9f7e6;
  color: #0f7a44;
}

.badge.ko {
  background: #ffe0e0;
  color: #9a1f2f;
}

.badge.type {
  background: #f4dbff;
  color: #672a85;
}

.details {
  width: 100%;
  margin: 0;
  padding-left: 1rem;
}

.danger {
  background: #ce304a;
  color: #fff;
  border-color: #ce304a;
}

.error {
  color: #c21f34;
}

@media (min-width: 980px) {
  .admin-page {
    padding: 2rem;
  }

  .inline-form {
    grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
  }
}
</style>

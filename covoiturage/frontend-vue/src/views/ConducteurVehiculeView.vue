<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Mon Véhicule</h1>
      <p class="subtitle">Enregistrez les informations de votre véhicule conducteur.</p>
    </header>

    <div class="glass vehicle-card">
      <form class="form-grid" @submit.prevent="submit">
        <div class="field">
          <label class="field-label">Marque</label>
          <input v-model="form.marque" class="field-input" placeholder="Ex: Renault" />
        </div>
        <div class="field">
          <label class="field-label">Modèle</label>
          <input v-model="form.modele" class="field-input" placeholder="Ex: Clio" />
        </div>
        <div class="field">
          <label class="field-label">Immatriculation</label>
          <input v-model="form.immatriculation" class="field-input" placeholder="Ex: 123 تونس 456" />
        </div>
        <div class="field">
          <label class="field-label">Photo véhicule</label>
          <input ref="photo" type="file" class="field-input file-input" accept="image/*" />
          <span v-if="photo?.files?.[0]" class="file-name">{{ photo.files[0].name }}</span>
        </div>
        <button class="primary-btn" type="submit">Enregistrer le véhicule</button>
      </form>
    </div>
  </section>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useProfilStore } from '@/stores/profilStore'
import { useRouter } from 'vue-router'

const profil = useProfilStore()
const router = useRouter()

const form = reactive({ marque: '', modele: '', immatriculation: '' })
const photo = ref<HTMLInputElement | null>(null)

const submit = async () => {
  const payload = { marque: form.marque, modele: form.modele, immatriculation: form.immatriculation }
  await profil.updateVehicule(payload)
  router.push('/profil')
}
</script>

<style scoped>
.page-shell {
  padding: 40px 24px;
  max-width: 1000px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
}

.vehicle-card {
  padding: 24px;
  border-radius: 16px;
}

.vehicle-card:hover {
  border-color: rgba(255, 225, 128, 0.5);
}

.form-grid {
  display: grid;
  gap: 16px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  color: #fdf9f0;
  font-size: 14px;
  font-weight: 500;
}

.field-input {
  width: 100%;
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 10px 14px;
  font-family: inherit;
  font-size: 14px;
}

.field-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.field-input:focus {
  outline: none;
  border-color: #ffe180;
}

.file-input {
  cursor: pointer;
}

.file-name {
  color: rgba(253, 249, 240, 0.6);
  font-size: 12px;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 10px 24px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

@media (max-width: 640px) {
  .page-title {
    font-size: 24px;
  }
}
</style>

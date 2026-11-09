<script setup lang="ts">
import { ref } from 'vue'
import notificationService from '@/services/notificationService'

const message = ref('')
const type = ref('info')
const isLoading = ref(false)
const success = ref<string | null>(null)
const error = ref<string | null>(null)

const roles = [
  { key: 'membre', label: 'Membre' },
  { key: 'etudiant', label: 'Étudiant' },
  { key: 'professionnel', label: 'Professionnel' },
  { key: 'conducteur', label: 'Conducteur' },
]

const send = async () => {
  isLoading.value = true
  error.value = null
  success.value = null
  try {
    const targetRoles = Array.from(
      document.querySelectorAll<HTMLInputElement>('input[name="target_roles"]:checked'),
    ).map((input) => input.value)
    const resp = await notificationService.broadcastNotification(message.value, type.value, targetRoles)
    success.value = `Notification envoyée à ${resp?.data?.sent_count ?? 0} membres`
    message.value = ''
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Erreur lors de l envoi'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <section class="page page-shell">
    <header class="page-header">
      <h1 class="page-title">Envoyer une notification</h1>
      <p class="subtitle">Diffuser un message aux utilisateurs ciblés.</p>
    </header>

    <div class="form-row">
      <label>Message</label>
      <textarea v-model="message" rows="6" />
    </div>

    <div class="form-row">
      <label>Type</label>
      <select v-model="type">
        <option value="info">Info</option>
        <option value="warning">Avertissement</option>
        <option value="alert">Alerte</option>
      </select>
    </div>

    <div class="form-row">
      <label>Destinataires</label>
      <div class="checkbox-list">
        <label v-for="role in roles" :key="role.key">
          <input type="checkbox" name="target_roles" :value="role.key" />
          {{ role.label }}
        </label>
      </div>
      <p class="hint">Les comptes "Chauffeur" ne peuvent pas recevoir ces diffusions.</p>
    </div>

    <div class="form-row">
      <button class="primary-btn" :disabled="isLoading" @click.prevent="send">Envoyer la notification</button>
    </div>

    <div v-if="success" class="status success">{{ success }}</div>
    <div v-if="error" class="status error">{{ error }}</div>
  </section>
</template>

<style scoped>
/* Card container */
.page.page-shell {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 16px;
  padding: 2rem;
  max-width: 800px;
  margin: 2rem auto;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.25);
  box-shadow: 0 6px 18px rgba(0,0,0,0.35);
  color: #fff;
}

/* Header */
.page-header .page-title {
  color: #ffffff;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
}
.page-header .subtitle {
  color: rgba(255,255,255,0.7);
  margin: 0 0 1rem 0;
}

/* Form rows and labels */
.form-row { margin-bottom: 16px }
.form-row > label {
  display: block;
  color: #ffffff;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

/* Inputs */
textarea,
select {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 8px;
  color: #ffffff;
  padding: 0.75rem;
  width: 100%;
  backdrop-filter: blur(4px);
  outline: none;
}

textarea::placeholder { color: rgba(255,255,255,0.6) }

/* Checkboxes styled with spacing and accent */
.checkbox-list {
  display: flex;
  gap: 1.5rem;
  align-items: center;
  flex-wrap: wrap;
}
.checkbox-list label {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  color: #ffffff;
  font-weight: 500;
}
.checkbox-list input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #f0c040;
}

.hint {
  color: rgba(255,255,255,0.5);
  font-size: 0.85rem;
  font-style: italic;
  margin-top: 0.5rem;
}

/* Primary (submit) button */
.primary-btn {
  background: #FFE180;
  color: #1a1a1a;
  font-weight: 700;
  border-radius: 8px;
  padding: 0.85rem 2rem;
  width: 100%;
  border: none;
  cursor: pointer;
  transition: background 120ms ease-in-out, transform 120ms ease;
}
.primary-btn:disabled { opacity: 0.7; cursor: not-allowed }
.primary-btn:hover:not(:disabled) { background: #8a7a52; transform: translateY(-1px) }

/* Status badges */
.status {
  margin-top: 12px;
  padding: 0.6rem 0.9rem;
  border-radius: 8px;
  font-weight: 600;
  display: inline-block;
}
.status.success {
  background: rgba(46, 204, 113, 0.12);
  color: #27ae60;
}
.status.error {
  background: rgba(231, 76, 60, 0.12);
  color: #c0392b;
}

/* Small responsive tweaks */
@media (max-width: 520px) {
  .page.page-shell { padding: 1.25rem; margin: 1rem }
  .page-header .page-title { font-size: 1.5rem }
}
</style>

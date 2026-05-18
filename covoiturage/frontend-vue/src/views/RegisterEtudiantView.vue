<template>
  <section class="auth-page">
    <div class="glass-card">
      <h1 class="auth-title">Inscription Étudiant</h1>
      <p class="auth-subtitle">Complétez votre profil étudiant pour accéder à la plateforme.</p>
      <div v-if="auth.error" class="error-message">{{ auth.error }}</div>
      <form @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label">Nom complet</label>
          <input v-model="name" type="text" class="form-input" required />
          <span v-if="auth.validationErrors?.name?.[0]" class="field-error">{{ auth.validationErrors.name[0] }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="email" type="email" class="form-input" required />
          <span v-if="auth.validationErrors?.email?.[0]" class="field-error">{{ auth.validationErrors.email[0] }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <input v-model="password" type="password" class="form-input" required />
          <span v-if="auth.validationErrors?.password?.[0]" class="field-error">{{ auth.validationErrors.password[0] }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Confirmer le mot de passe</label>
          <input v-model="password_confirmation" type="password" class="form-input" required />
          <span v-if="auth.validationErrors?.password_confirmation?.[0]" class="field-error">{{ auth.validationErrors.password_confirmation[0] }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input v-model="phone" type="tel" class="form-input" />
          <span v-if="auth.validationErrors?.phone?.[0]" class="field-error">{{ auth.validationErrors.phone[0] }}</span>
        </div>
        <div class="form-group">
          <label class="form-label">Carte étudiante (jpg, png, pdf)</label>
          <input
            type="file"
            class="file-input"
            accept="image/*,.pdf"
            required
            @change="(e) => carteEtudiante = (e.target as HTMLInputElement).files?.[0] ?? null"
          />
          <span v-if="auth.validationErrors?.carte_etudiante?.[0]" class="field-error">{{ auth.validationErrors.carte_etudiante[0] }}</span>
          <span v-if="carteEtudiante" class="file-name">{{ carteEtudiante.name }}</span>
        </div>
        <button class="primary-btn" type="submit" :disabled="isLoading">
          {{ isLoading ? 'Inscription en cours...' : "S'inscrire" }}
        </button>
      </form>
      <p class="secondary-link">
        Vous avez déjà un compte? <RouterLink to="/login">Se connecter</RouterLink>
      </p>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const phone = ref('')
const carteEtudiante = ref<File | null>(null)
const isLoading = ref(false)
// Local view-level error banner is driven by the store's `auth.error`.

const submit = async () => {
  isLoading.value = true

  try {
    if (!carteEtudiante.value) {
      auth.error = 'Veuillez télécharger votre carte étudiante.'
      return
    }

    const fd = new FormData()
    fd.append('name', name.value)
    fd.append('email', email.value)
    fd.append('password', password.value)
    fd.append('password_confirmation', password_confirmation.value)
    if (phone.value) fd.append('phone', phone.value)
    fd.append('carte_etudiante', carteEtudiante.value)

    console.log('[register] FormData entries:')
    for (const [key, value] of fd.entries()) {
      console.log(key, typeof value === 'object' ? `FILE: ${(value as File).name} (${(value as File).size} bytes)` : value)
    }

    await auth.registerEtudiant(fd)
    router.push('/trajets')
  } catch (err: any) {
    // store now populates `auth.validationErrors` and `auth.error`; no duplicate extraction here
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.glass-card {
  background: rgba(253, 249, 240, 0.08);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 24px;
  padding: 48px;
  max-width: 460px;
  width: 100%;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

.auth-title {
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
  text-align: center;
}

.auth-subtitle {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0 0 32px 0;
  text-align: center;
}

form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  color: #fdf9f0;
  font-size: 14px;
  font-weight: 500;
}

.form-input {
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 12px 16px;
  width: 100%;
  font-family: inherit;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.form-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.form-input:focus {
  outline: none;
  border-color: #ffe180;
  box-shadow: 0 0 0 3px rgba(255, 225, 128, 0.12);
}

.file-input {
  background: rgba(253, 249, 240, 0.07);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 12px 16px;
  width: 100%;
  font-family: inherit;
  font-size: 14px;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.file-input:focus {
  outline: none;
  border-color: #ffe180;
  box-shadow: 0 0 0 3px rgba(255, 225, 128, 0.12);
}

.file-name {
  color: rgba(253, 249, 240, 0.6);
  font-size: 12px;
  margin-top: 4px;
}

.error-message {
  background-color: rgba(220, 38, 38, 0.1);
  border: 1px solid rgba(220, 38, 38, 0.5);
  color: #fca5a5;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 14px;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 14px;
  width: 100%;
  cursor: pointer;
  font-size: 15px;
  margin-top: 8px;
  transition: all 0.2s ease;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.primary-btn:active {
  transform: scale(0.98);
}

.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.secondary-link {
  margin: 16px 0 0 0;
  text-align: center;
  font-size: 14px;
}

.secondary-link a {
  color: #9f9065;
  text-decoration: none;
  transition: color 0.2s ease;
}

.secondary-link a:hover {
  color: #ffe180;
}

.field-error { color: #fca5a5; font-size: 12px; margin-top: 2px; }

@media (max-width: 480px) {
  .glass-card {
    padding: 32px 24px;
  }

  .auth-title {
    font-size: 24px;
  }
}
</style>

<template>
  <section class="auth-page">
    <div class="glass-card">
      <h1 class="auth-title">Espace Chauffeur</h1>
      <p class="auth-subtitle">Connectez-vous avec vos identifiants</p>
      <form @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="email" type="email" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <input v-model="password" type="password" class="form-input" required />
        </div>
        <button class="primary-btn" type="submit">Se connecter</button>
      </form>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const auth = useAuthStore()
const router = useRouter()

const roleLandingPage: Record<string, string> = {
  membre: '/trajets',
  conducteur: '/dashboard',
  chauffeur_bus: '/chauffeur/bus',
  admin: '/admin',
}

const email = ref('')
const password = ref('')

const submit = async () => {
  await auth.login({ email: email.value, password: password.value })
  router.push(roleLandingPage[auth.role || 'membre'] || '/trajets')
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
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
}

.auth-title {
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px;
  text-align: center;
}

.auth-subtitle {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0 0 32px;
  text-align: center;
}

form {
  display: grid;
  gap: 24px;
}

.form-group {
  display: grid;
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
}

.form-input::placeholder {
  color: rgba(253, 249, 240, 0.35);
}

.form-input:focus {
  outline: none;
  border-color: #ffe180;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 14px;
  width: 100%;
  font-size: 15px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

@media (max-width: 480px) {
  .glass-card {
    padding: 32px 24px;
  }

  .auth-title {
    font-size: 24px;
  }
}
</style>

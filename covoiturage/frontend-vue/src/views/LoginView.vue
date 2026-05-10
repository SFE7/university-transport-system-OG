<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  email: '',
  password: '',
})

const errorMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  try {
    await authStore.login(form.value)
    router.push('/trajets')
  } catch {
    errorMessage.value = authStore.error || 'Login failed'
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
  gap: 24px;
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
  transition: all 0.2s ease;
}

.primary-btn:hover:not(:disabled) {
  background: #9f9065;
  color: #fdf9f0;
}

.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.primary-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.error-message {
  color: #ff6b6b;
  font-size: 13px;
  margin: 0;
  text-align: center;
}

.secondary-link {
  margin: 0;
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

@media (max-width: 480px) {
  .glass-card {
    padding: 32px 24px;
  }

  .auth-title {
    font-size: 24px;
  }
}
</style>

<template>
  <section class="auth-page">
    <div class="glass-card">
      <h1 class="auth-title">Connexion</h1>
      <p class="auth-subtitle">Accedez a vos trajets et reservations.</p>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input v-model="form.email" type="email" class="form-input" required />
        </div>
        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <input v-model="form.password" type="password" class="form-input" required />
        </div>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <button class="primary-btn" type="submit" :disabled="authStore.isLoading">
          {{ authStore.isLoading ? 'Chargement...' : 'Se connecter' }}
        </button>
      </form>
      <p class="secondary-link">
        Pas encore de compte? <RouterLink to="/register">S'inscrire</RouterLink>
      </p>
    </div>
  </section>
</template>

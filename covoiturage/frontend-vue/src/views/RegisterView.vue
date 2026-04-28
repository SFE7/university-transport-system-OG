<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'membre',
})

const errorMessage = ref('')

const handleSubmit = async () => {
  errorMessage.value = ''
  try {
    await authStore.register(form.value)
    router.push('/trajets')
  } catch {
    errorMessage.value = authStore.error || 'Registration failed'
  }
}
</script>

<template>
  <section class="page">
    <div class="card">
      <h1 class="page-title">Creer un compte</h1>
      <p class="subtitle">Choisissez votre role et partagez vos trajets.</p>
      <form class="form-row" @submit.prevent="handleSubmit">
        <label>
          Nom complet
          <input v-model="form.name" type="text" class="input" required />
        </label>
        <label>
          Email
          <input v-model="form.email" type="email" class="input" required />
        </label>
        <div class="grid two">
          <label>
            Mot de passe
            <input v-model="form.password" type="password" class="input" required />
          </label>
          <label>
            Confirmation
            <input v-model="form.password_confirmation" type="password" class="input" required />
          </label>
        </div>
        <label>
          Role
          <select v-model="form.role" class="select">
            <option value="membre">Membre</option>
            <option value="conducteur">Conducteur</option>
          </select>
        </label>
        <div class="form-actions">
          <button class="btn" type="submit" :disabled="authStore.isLoading">Creer</button>
          <span v-if="authStore.isLoading" class="status">Chargement...</span>
        </div>
        <p v-if="errorMessage" class="status">{{ errorMessage }}</p>
      </form>
    </div>
  </section>
</template>

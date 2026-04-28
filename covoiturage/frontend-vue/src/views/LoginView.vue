<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
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

<template>
  <section class="page">
    <div class="card">
      <h1 class="page-title">Connexion</h1>
      <p class="subtitle">Accedez a vos trajets et reservations.</p>
      <form class="form-row" @submit.prevent="handleSubmit">
        <div class="form-row">
          <label>
            Email
            <input v-model="form.email" type="email" class="input" required />
          </label>
          <label>
            Mot de passe
            <input v-model="form.password" type="password" class="input" required />
          </label>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit" :disabled="authStore.isLoading">Se connecter</button>
          <span v-if="authStore.isLoading" class="status">Chargement...</span>
        </div>
        <p v-if="errorMessage" class="status">{{ errorMessage }}</p>
      </form>
    </div>
  </section>
</template>

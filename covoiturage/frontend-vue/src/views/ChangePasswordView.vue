<template>
  <div>
    <h1>Changer le mot de passe</h1>
    <form @submit.prevent="submit">
      <div>
        <label>Mot de passe actuel</label>
        <input v-model="current_password" type="password" required />
      </div>
      <div>
        <label>Nouveau mot de passe</label>
        <input v-model="password" type="password" required />
      </div>
      <div>
        <label>Confirmer</label>
        <input v-model="password_confirmation" type="password" required />
      </div>
      <button type="submit">Mettre à jour</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const current_password = ref('')
const password = ref('')
const password_confirmation = ref('')

const submit = async () => {
  await auth.changePassword({ current_password: current_password.value, password: password.value, password_confirmation: password_confirmation.value })
  router.push('/profil')
}
</script>

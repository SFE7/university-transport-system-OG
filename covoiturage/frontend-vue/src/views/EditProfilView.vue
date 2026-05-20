<template>
  <div>
    <h1>Éditer le profil</h1>
    <form @submit.prevent="submit">
      <div>
        <label>Nom</label>
        <input v-model="form.name" />
      </div>
      <div>
        <label>Email</label>
        <input v-model="form.email" type="email" />
      </div>
      <div>
        <label>Téléphone</label>
        <input v-model="form.phone" />
      </div>
      <button type="submit">Enregistrer</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useProfilStore } from '@/stores/profilStore'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'

const profil = useProfilStore()
const auth = useAuthStore()
const router = useRouter()

const form = reactive({ name: auth.membre?.name || '', email: auth.membre?.email || '', phone: auth.membre?.phone || '' })

const submit = async () => {
  await profil.updateProfile(form)
  router.push('/profil')
}
</script>

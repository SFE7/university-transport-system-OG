<template>
  <div>
    <h1>Profil</h1>
    <div v-if="profile">
      <p><strong>Nom:</strong> {{ profile.name }}</p>
      <p><strong>Email:</strong> {{ profile.email }}</p>
      <p><strong>Téléphone:</strong> {{ profile.phone }}</p>
      <p><strong>Role:</strong> {{ profile.role }}</p>
      <router-link to="/membres/profil/edit">Éditer le profil</router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useProfilStore } from '@/stores/profilStore'
import { useAuthStore } from '@/stores/authStore'

const profil = useProfilStore()
const auth = useAuthStore()

onMounted(() => {
  const id = auth.membre?.id
  if (id) profil.fetchProfile(id)
})

const profile = profil.profile
</script>

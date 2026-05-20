<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import profileService from '@/services/profilService'
import authService from '@/services/authService'

const authStore = useAuthStore()

const profileForm = reactive({
  name: '',
  email: '',
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const profileMessage = ref('')
const profileError = ref('')
const passwordMessage = ref('')
const passwordError = ref('')

const member = computed(() => authStore.membre)

const hydrateProfileForm = () => {
  profileForm.name = member.value?.name || ''
  profileForm.email = member.value?.email || ''
}

watch(member, hydrateProfileForm, { immediate: true })

const saveProfile = async () => {
  profileMessage.value = ''
  profileError.value = ''

  try {
    const response = await profileService.updateProfile({
      name: profileForm.name,
      email: profileForm.email,
    })

    const updatedMember = response.data
    authStore.membre = updatedMember
    localStorage.setItem('auth_membre', JSON.stringify(updatedMember))
    profileMessage.value = 'Informations personnelles mises à jour.'
  } catch (err: any) {
    profileError.value = err?.response?.data?.message || 'Impossible de sauvegarder le profil.'
  }
}

const changePassword = async () => {
  passwordMessage.value = ''
  passwordError.value = ''

  try {
    await authService.changePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation,
    })

    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    passwordMessage.value = 'Mot de passe mis à jour.'
  } catch (err: any) {
    const responseErrors = err?.response?.data?.errors
    passwordError.value =
      err?.response?.data?.message ||
      responseErrors?.current_password?.[0] ||
      responseErrors?.password?.[0] ||
      'Impossible de changer le mot de passe.'
  }
}
</script>

<template>
  <section class="profile-page">
    <header class="profile-header">
      <h1>Mon profil</h1>
      <p>Gerez vos informations personnelles et votre mot de passe.</p>
    </header>

    <div class="profile-grid">
      <section class="profile-card">
        <h2>Informations personnelles</h2>
        <form class="profile-form" @submit.prevent="saveProfile">
          <label>
            <span>Nom</span>
            <input v-model="profileForm.name" type="text" placeholder="Votre nom" />
          </label>
          <label>
            <span>Email</span>
            <input v-model="profileForm.email" type="email" placeholder="Votre email" />
          </label>
          <p v-if="profileError" class="error-message">{{ profileError }}</p>
          <p v-if="profileMessage" class="success-message">{{ profileMessage }}</p>
          <button type="submit" class="primary-btn">Enregistrer</button>
        </form>
      </section>

      <section class="profile-card">
        <h2>Changer le mot de passe</h2>
        <form class="profile-form" @submit.prevent="changePassword">
          <label>
            <span>Mot de passe actuel</span>
            <input v-model="passwordForm.current_password" type="password" placeholder="Mot de passe actuel" />
          </label>
          <label>
            <span>Nouveau mot de passe</span>
            <input v-model="passwordForm.password" type="password" placeholder="Nouveau mot de passe" />
          </label>
          <label>
            <span>Confirmation</span>
            <input v-model="passwordForm.password_confirmation" type="password" placeholder="Confirmer le mot de passe" />
          </label>
          <p v-if="passwordError" class="error-message">{{ passwordError }}</p>
          <p v-if="passwordMessage" class="success-message">{{ passwordMessage }}</p>
          <button type="submit" class="primary-btn">Mettre à jour</button>
        </form>
      </section>
    </div>
  </section>
</template>

<style scoped>
.profile-page {
  max-width: 980px;
  margin: 0 auto;
  padding: 40px 24px 64px;
}

.profile-header {
  margin-bottom: 24px;
}

.profile-header h1 {
  margin: 0;
  color: #ffe180;
  font-size: 32px;
}

.profile-header p {
  margin: 8px 0 0;
  color: rgba(253, 249, 240, 0.72);
}

.profile-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.profile-card {
  background: rgba(253, 249, 240, 0.06);
  border: 1px solid rgba(253, 249, 240, 0.15);
  border-radius: 20px;
  padding: 24px;
}

.profile-card h2 {
  margin: 0 0 16px;
  color: #fdf9f0;
  font-size: 20px;
}

.profile-form {
  display: grid;
  gap: 14px;
}

.profile-form label {
  display: grid;
  gap: 6px;
}

.profile-form span {
  color: rgba(253, 249, 240, 0.72);
  font-size: 14px;
}

.profile-form input {
  background: rgba(253, 249, 240, 0.08);
  border: 1px solid rgba(253, 249, 240, 0.16);
  border-radius: 12px;
  color: #fdf9f0;
  padding: 12px 14px;
}

.profile-form input:focus {
  outline: none;
  border-color: #ffe180;
}

.primary-btn {
  justify-self: start;
}

.error-message {
  margin: 0;
  color: #ff6b6b;
}

.success-message {
  margin: 0;
  color: #6ec47a;
}

@media (min-width: 900px) {
  .profile-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>

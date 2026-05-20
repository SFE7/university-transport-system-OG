<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useRouter } from 'vue-router'

const router = useRouter()

const registrationOptions = [
  {
    key: 'etudiant',
    title: 'Étudiant',
    description: 'Inscrivez-vous comme étudiant et trouvez vos trajets.',
    icon: '🎓',
    path: '/register/etudiant',
  },
  {
    key: 'professionnel',
    title: 'Professionnel',
    description: 'Rejoignez nos professionnels pour partager vos trajets.',
    icon: '💼',
    path: '/register/professionnel',
  },
  {
    key: 'conducteur',
    title: 'Conducteur',
    description: 'Proposez vos trajets comme conducteur.',
    icon: '🚗',
    path: '/register/conducteur',
  },
]

const handleSelectRole = (path: string) => {
  router.push(path)
}
</script>

<template>
  <section class="auth-page">
    <div class="landing-container">
      <h1 class="auth-title">Créer un compte</h1>
      <p class="auth-subtitle">Choisissez votre rôle et commencez à partager vos trajets.</p>
      <div class="cards-grid">
        <div v-for="option in registrationOptions" :key="option.key" class="role-card">
          <div class="icon">{{ option.icon }}</div>
          <h2 class="role-title">{{ option.title }}</h2>
          <p class="role-description">{{ option.description }}</p>
          <button class="primary-btn" @click="handleSelectRole(option.path)">
            S'inscrire
          </button>
        </div>
      </div>
      <p class="secondary-link">
        Vous avez déjà un compte? <RouterLink to="/login">Se connecter</RouterLink>
      </p>
    </div>
  </section>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.landing-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
}

.auth-title {
  color: #ffe180;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 12px 0;
  text-align: center;
}

.auth-subtitle {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0 0 48px 0;
  text-align: center;
  max-width: 500px;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  max-width: 1000px;
  width: 100%;
  margin-bottom: 32px;
}

.role-card {
  background: rgba(253, 249, 240, 0.08);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 24px;
  padding: 36px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.role-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 225, 128, 0.4);
}

.icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.role-title {
  color: #ffe180;
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 12px 0;
}

.role-description {
  color: rgba(253, 249, 240, 0.6);
  font-size: 14px;
  margin: 0 0 24px 0;
  line-height: 1.5;
}

.primary-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: none;
  border-radius: 999px;
  font-weight: 700;
  padding: 14px 32px;
  cursor: pointer;
  font-size: 15px;
  transition: all 0.2s ease;
  align-self: center;
}

.primary-btn:hover {
  background: #9f9065;
  color: #fdf9f0;
}

.primary-btn:active {
  transform: scale(0.98);
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

@media (max-width: 768px) {
  .cards-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .auth-title {
    font-size: 24px;
  }
}
</style>

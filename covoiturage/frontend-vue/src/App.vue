<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

authStore.initFromStorage()

onMounted(async () => {
  const valid = await authStore.verifyToken()
  if (!valid && route.meta.requiresAuth) {
    router.replace({ name: 'login', query: { redirect: route.fullPath } })
  }
})

const displayName = computed(() => authStore.membre?.name || 'Invite')
const isAdmin = computed(() => authStore.membre?.role === 'admin')
const isBusDriver = computed(() => authStore.membre?.role === 'chauffeur_bus')
const accessDeniedMessage = computed(() =>
  route.query.error === '403' ? 'Acces refuse (403) : cette page est reservee a votre role.' : '',
)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="app-shell">
    <div class="navbar-layout">
      <RouterLink class="brand-fixed" to="/trajets">
        <img src="/TrajetU.png" alt="TrajetU Logo" class="logo-img" />
      </RouterLink>

      <header class="topbar" :class="{ admin: isAdmin }">
        <nav class="nav-links">
          <template v-if="String(authStore.membre?.role) === 'admin'">
            <RouterLink to="/admin/lignes">Lignes</RouterLink>
            <RouterLink to="/admin/incidents">Incidents</RouterLink>
            <RouterLink to="/admin/chauffeurs">Chauffeurs</RouterLink>
            <RouterLink to="/admin/arrets">Arrets</RouterLink>
            <RouterLink to="/admin/horaires">Horaires</RouterLink>
            <RouterLink to="/admin/documents">Documents</RouterLink>
            <RouterLink to="/admin/signalements">Signalements</RouterLink>
            <RouterLink to="/admin/notifications">Notifications</RouterLink>
            <RouterLink to="/admin/membres">Membres</RouterLink>
            <RouterLink to="/admin/statistiques">Statistiques</RouterLink>
          </template>
          <template v-else-if="isBusDriver">
            <RouterLink to="/chauffeur/bus">Partager position</RouterLink>
          </template>
          <template v-else>
            <RouterLink v-if="!authStore.isAuthenticated || authStore.membre?.role === 'membre'" to="/trajets">Trajets</RouterLink>
            <RouterLink v-if="authStore.membre?.role === 'membre'" to="/reservations">Reservations</RouterLink>
            <RouterLink v-if="authStore.isAuthenticated" to="/history">Historique</RouterLink>
            <RouterLink v-if="authStore.isAuthenticated" to="/notifications">Notifications</RouterLink>
            <RouterLink v-if="authStore.isConducteur" to="/dashboard">Dashboard</RouterLink>
            <RouterLink v-if="authStore.membre?.role === 'membre'" to="/chauffeur/bus/map">Map</RouterLink>
            <RouterLink v-if="authStore.membre?.role === 'membre'" to="/chauffeur/bus/schedules">Horaires</RouterLink>
            <!-- Comparer feature removed -->
          </template>
          <RouterLink v-if="authStore.isAuthenticated" to="/profil">Mon profil</RouterLink>
        </nav>
      </header>

      <div class="auth-actions-fixed" :class="{ admin: isAdmin }">
        <span class="welcome">Salut, {{ displayName }}</span>
        <RouterLink v-if="!authStore.isAuthenticated" class="btn ghost" to="/login">Login</RouterLink>
        <RouterLink v-if="!authStore.isAuthenticated" class="btn" to="/register">Register</RouterLink>
        <button v-if="authStore.isAuthenticated" class="btn logout-btn" type="button" @click="handleLogout">
          Logout
        </button>
      </div>
    </div>
    <main class="content">
      <div v-if="accessDeniedMessage" class="alert-banner">
        {{ accessDeniedMessage }}
      </div>
      <RouterView />
    </main>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
  --color-primary: #1b3d2f;
  --color-surface: #fdf9f0;
  --color-gold: #9f9065;
  --color-yellow: #ffe180;
  --color-text: #1b3d2f;
  --color-text-light: #fdf9f0;
  --bg: #1b3d2f;
  --bg-alt: #2d5a42;
  --ink: #fdf9f0;
  --muted: rgba(253, 249, 240, 0.72);
  --primary: #9f9065;
  --primary-dark: #ffe180;
  --accent: #ffe180;
  --card: rgba(253, 249, 240, 0.08);
  --border: rgba(253, 249, 240, 0.15);
  --shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

* {
  box-sizing: border-box;
}

html,
body,
#app {
  min-height: 100%;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', sans-serif;
  color: var(--color-text-light);
  background-image: url('/campus-bg.jpg');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  min-height: 100vh;
  overflow-x: hidden;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: rgba(20, 80, 40, 0.55);
  pointer-events: none;
  z-index: 0;
}

a {
  color: inherit;
  text-decoration: none;
}

.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.navbar-layout {
  position: relative;
  height: 0;
  z-index: 1000;
}

.brand-fixed {
  position: fixed;
  top: 24px;
  left: 24px;
  color: #ffe180;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.02em;
  transition: color 0.2s ease, transform 0.2s ease;
  white-space: nowrap;
  z-index: 1001;
  display: flex;
  align-items: center;
}

.logo-img {
  height: 100px;
  width: auto;
  object-fit: contain;
  transition: transform 0.2s ease;
}

.brand-fixed:hover {
  transform: scale(1.02);
}

.brand-fixed:hover .logo-img {
  transform: scale(1.02);
}

.brand-fixed:active {
  transform: scale(0.97);
}

.topbar {
  position: fixed;
  top: 24px;
  left: 50%;
  transform: translateX(-50%);
  width: auto;
  max-width: 90vw;
  z-index: 1000;
  backdrop-filter: blur(40px) saturate(220%);
  -webkit-backdrop-filter: blur(40px) saturate(220%);
  background: rgba(253, 249, 240, 0.08);
  border: 1px solid rgba(253, 249, 240, 0.18);
  border-radius: 999px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 24px;
}

.topbar.admin {
  width: calc(100vw - 380px);
  max-width: calc(100vw - 380px);
}

.nav-links {
  display: flex;
  gap: 8px;
  font-weight: 500;
  color: rgba(253, 249, 240, 0.75);
  font-size: 14px;
}

.nav-links a {
  padding: 6px 12px;
  border-radius: 999px;
  transition: color 0.2s ease, background 0.2s ease, opacity 0.2s ease, transform 0.2s ease;
  opacity: 1;
}

.nav-links a:active {
  transform: scale(0.97);
}

.nav-links a:hover {
  color: #ffe180;
  background: rgba(255, 225, 128, 0.1);
  opacity: 1;
}

.nav-links a.router-link-active,
.nav-links a.router-link-exact-active {
  color: #ffe180;
  font-weight: 600;
  opacity: 1;
}

.auth-actions-fixed {
  position: fixed;
  top: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  z-index: 1001;
}

.auth-actions-fixed.admin {
  top: 24px;
  width: 170px;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 4px 6px;
}

.welcome {
  color: #9f9065;
  font-size: 18px;
  font-weight: 700;
  white-space: nowrap;
  margin-right: 8px;
}

.auth-actions-fixed.admin .welcome {
  flex: 0 0 100%;
  margin-right: 0;
  text-align: right;
}

.content {
  flex: 1;
  padding: 80px 6vw 64px;
}

.alert-banner {
  margin-bottom: 20px;
  padding: 14px 18px;
  border-radius: 16px;
  background: rgba(253, 249, 240, 0.08);
  border: 1px solid rgba(253, 249, 240, 0.14);
  color: var(--color-text-light);
  font-weight: 600;
  backdrop-filter: blur(20px) saturate(180%);
}

.page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-title {
  font-family: 'Inter', sans-serif;
  font-size: clamp(1.8rem, 2.8vw, 2.6rem);
  margin: 0;
  font-weight: 700;
}

.subtitle {
  color: var(--muted);
  max-width: 600px;
}

.grid {
  display: grid;
  gap: 20px;
}

.grid.two {
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.grid.three {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.glass,
.card {
  background: rgba(253, 249, 240, 0.1);
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  padding: 22px;
  color: var(--color-text-light);
}

.card.soft {
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  background: rgba(255, 225, 128, 0.14);
  color: var(--color-yellow);
  border: 1px solid rgba(255, 225, 128, 0.18);
}

.badge.gray {
  background: rgba(253, 249, 240, 0.1);
  color: var(--color-text-light);
}

.badge.dark {
  background: rgba(27, 61, 47, 0.28);
  color: var(--color-text-light);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 600;
  background: rgba(253, 249, 240, 0.12);
  color: var(--color-text-light);
  border: 1px solid rgba(253, 249, 240, 0.16);
  backdrop-filter: blur(14px);
  cursor: pointer;
  transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.14);
}

.btn:hover {
  transform: translateY(-1px);
  background: rgba(253, 249, 240, 0.18);
  border-color: rgba(255, 225, 128, 0.35);
}

.btn:active {
  transform: scale(0.97);
}

.btn.ghost {
  background: transparent;
  color: var(--color-text-light);
  box-shadow: none;
  border: 1px solid rgba(253, 249, 240, 0.14);
}

.btn.logout-btn {
  background: #ffe180;
  color: #1b3d2f;
  border: 1px solid rgba(255, 225, 128, 0.2);
  border-radius: 999px;
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 700;
  box-shadow: 0 10px 24px rgba(255, 225, 128, 0.16);
}

.btn.logout-btn:hover {
  background: #9f9065;
  color: #1b3d2f;
  border-color: rgba(159, 144, 101, 0.4);
}

.input,
.select,
.textarea {
  width: 100%;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid rgba(253, 249, 240, 0.16);
  background: rgba(253, 249, 240, 0.08);
  color: var(--color-text-light);
  font-family: inherit;
  backdrop-filter: blur(18px) saturate(180%);
  -webkit-backdrop-filter: blur(18px) saturate(180%);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.input:focus,
.select:focus,
.textarea:focus {
  outline: none;
  border-color: rgba(255, 225, 128, 0.45);
  box-shadow: 0 0 0 3px rgba(255, 225, 128, 0.18);
}

.form-row {
  display: grid;
  gap: 14px;
}

.form-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.status {
  font-weight: 600;
  color: var(--muted);
}

.muted {
  color: var(--muted);
}

.divider {
  height: 1px;
  background: rgba(253, 249, 240, 0.14);
  width: 100%;
}

.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

@media (max-width: 900px) {
  .topbar {
    flex-direction: column;
    align-items: flex-start;
    padding: 16px 5vw;
    width: auto;
    max-width: 90vw;
  }

  .topbar.admin {
    width: auto;
    max-width: 90vw;
  }

  .nav-links {
    flex-wrap: wrap;
  }

  .auth-actions-fixed,
  .auth-actions-fixed.admin {
    position: static;
    margin: 104px 24px 0 auto;
    justify-content: flex-end;
    flex-wrap: wrap;
    width: calc(100% - 48px);
  }
}
</style>

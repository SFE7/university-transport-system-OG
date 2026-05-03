<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

authStore.initFromStorage()

const displayName = computed(() => authStore.membre?.name || 'Invite')

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="app-shell">
    <header class="topbar">
      <RouterLink class="brand" to="/trajets">Covoiturage Universitaire</RouterLink>
      <nav class="nav-links">
        <RouterLink to="/trajets">Trajets</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/reservations">Reservations</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/history">Historique</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/notifications">Notifications</RouterLink>
        <RouterLink v-if="authStore.isConducteur" to="/dashboard">Dashboard</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated && ['chauffeur_bus', 'conducteur'].includes(authStore.membre?.role ?? '')" to="/bus/drive">Partager position</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/bus/map">Carte bus</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/bus/schedules">Horaires</RouterLink>
        <RouterLink v-if="authStore.isAuthenticated" to="/compare">Comparer</RouterLink>
        <RouterLink v-if="String(authStore.membre?.role) === 'admin'" to="/admin">Admin</RouterLink>
      </nav>
      <div class="auth-actions">
        <span class="welcome">Salut, {{ displayName }}</span>
        <RouterLink v-if="!authStore.isAuthenticated" class="btn ghost" to="/login">Login</RouterLink>
        <RouterLink v-if="!authStore.isAuthenticated" class="btn" to="/register">Register</RouterLink>
        <button v-if="authStore.isAuthenticated" class="btn" type="button" @click="handleLogout">
          Logout
        </button>
      </div>
    </header>
    <main class="content">
      <RouterView />
    </main>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap');

:root {
  --bg: #f6f4ef;
  --bg-alt: #efeae2;
  --ink: #1f1a13;
  --muted: #6b5f51;
  --primary: #d86b3a;
  --primary-dark: #b4562f;
  --accent: #2f6b73;
  --card: rgba(255, 255, 255, 0.72);
  --border: rgba(31, 26, 19, 0.12);
  --shadow: 0 18px 45px rgba(33, 26, 20, 0.12);
}

* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: 'Space Grotesk', sans-serif;
  color: var(--ink);
  background: radial-gradient(circle at top, #fff6e9 0%, var(--bg) 40%, var(--bg-alt) 100%);
  min-height: 100vh;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: linear-gradient(transparent 95%, rgba(0, 0, 0, 0.03) 95%),
    linear-gradient(90deg, transparent 95%, rgba(0, 0, 0, 0.03) 95%);
  background-size: 26px 26px;
  pointer-events: none;
  opacity: 0.5;
}

a {
  color: inherit;
  text-decoration: none;
}

.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 10;
  backdrop-filter: blur(18px);
  background: rgba(246, 244, 239, 0.85);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 6vw;
  gap: 16px;
}

.brand {
  font-family: 'Fraunces', serif;
  font-size: 1.2rem;
  letter-spacing: -0.02em;
  transition: all 0.2s ease;
}

.brand:active {
  transform: scale(0.97);
}

.nav-links {
  display: flex;
  gap: 18px;
  font-weight: 500;
  color: var(--muted);
}

.nav-links a {
  transition: all 0.2s ease;
}

.nav-links a:active {
  transform: scale(0.97);
}

.nav-links a.router-link-active {
  color: var(--ink);
}

.auth-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.welcome {
  color: var(--muted);
  font-size: 0.95rem;
}

.content {
  flex: 1;
  padding: 40px 6vw 64px;
}

.page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-title {
  font-family: 'Fraunces', serif;
  font-size: clamp(1.8rem, 2.8vw, 2.6rem);
  margin: 0;
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

.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: var(--shadow);
  padding: 22px;
  backdrop-filter: blur(12px);
}

.card.soft {
  box-shadow: 0 10px 30px rgba(33, 26, 20, 0.08);
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  background: rgba(216, 107, 58, 0.12);
  color: var(--primary-dark);
}

.badge.gray {
  background: rgba(47, 107, 115, 0.12);
  color: var(--accent);
}

.badge.dark {
  background: rgba(31, 26, 19, 0.1);
  color: var(--ink);
}

.btn {
  border: none;
  border-radius: 999px;
  padding: 10px 18px;
  font-weight: 600;
  background: var(--primary);
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 10px 20px rgba(216, 107, 58, 0.2);
}

.btn:hover {
  transform: translateY(-1px);
  background: var(--primary-dark);
}

.btn:active {
  transform: scale(0.97);
}

.btn.ghost {
  background: transparent;
  color: var(--ink);
  box-shadow: none;
  border: 1px solid var(--border);
}

.input,
.select,
.textarea {
  width: 100%;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.85);
  font-family: inherit;
  transition: all 0.2s ease;
}

.input:focus,
.select:focus,
.textarea:focus {
  outline: none;
  border-color: rgba(216, 107, 58, 0.45);
  box-shadow: 0 0 0 3px rgba(216, 107, 58, 0.15);
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
  background: var(--border);
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
  }

  .nav-links {
    flex-wrap: wrap;
  }
}

@media (prefers-color-scheme: dark) {
  :root {
    --bg: #151311;
    --bg-alt: #1a1714;
    --ink: #f8f2ea;
    --muted: #b8a89a;
    --card: rgba(34, 30, 26, 0.85);
    --border: rgba(248, 242, 234, 0.1);
    --shadow: 0 18px 45px rgba(0, 0, 0, 0.45);
  }

  body {
    background: radial-gradient(circle at top, #2a211b 0%, var(--bg) 50%, var(--bg-alt) 100%);
  }

  .topbar {
    background: rgba(21, 19, 17, 0.9);
  }

  .input,
  .select,
  .textarea {
    background: rgba(30, 27, 24, 0.9);
    color: var(--ink);
  }
}
</style>

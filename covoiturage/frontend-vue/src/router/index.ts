import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

type AppRole = 'membre' | 'conducteur' | 'chauffeur_bus' | 'admin'

type RouteMeta = {
  requiresAuth?: boolean
  guest?: boolean
  allowedRoles?: AppRole[]
}

const roleLandingPage: Record<AppRole, string> = {
  membre: '/trajets',
  conducteur: '/dashboard',
  chauffeur_bus: '/chauffeur/bus',
  admin: '/admin',
}

const routes = [
  { path: '/', redirect: '/trajets' },
  { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('@/views/RegisterView.vue'), meta: { guest: true } },
  { path: '/login/chauffeur', component: () => import('@/views/LoginChauffeurView.vue'), meta: { guest: true } },
  { path: '/register/etudiant', component: () => import('@/views/RegisterEtudiantView.vue'), meta: { guest: true } },
  { path: '/register/professionnel', component: () => import('@/views/RegisterProfessionnelView.vue'), meta: { guest: true } },
  { path: '/register/conducteur', component: () => import('@/views/RegisterConducteurView.vue'), meta: { guest: true } },
  { path: '/trajets', component: () => import('@/views/TrajetsView.vue'), meta: { allowedRoles: ['membre'] } },
  { path: '/trajets/:id', component: () => import('@/views/TrajetDetailView.vue'), meta: { allowedRoles: ['membre'] } },
  {
    path: '/reservations',
    component: () => import('@/views/MyReservationsView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre'] },
  },
  {
    path: '/dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['conducteur'] },
  },
  { path: '/membres/:id/avis', component: () => import('@/views/ConducteurProfileView.vue') },
  { path: '/profil', component: () => import('@/views/ProfileView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur', 'chauffeur_bus', 'admin'] } },
  { path: '/membres/profil/edit', component: () => import('@/views/EditProfilView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] } },
  { path: '/profil/password', component: () => import('@/views/ChangePasswordView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur', 'chauffeur_bus'] } },
  {
    path: '/documents/pending',
    component: () => import('@/views/DocumentsPendingView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur', 'chauffeur_bus', 'admin'] },
  },
  {
    path: '/history',
    component: () => import('@/views/TripHistoryView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] },
  },
  {
    path: '/notifications',
    component: () => import('@/views/NotificationsView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] },
  },
  {
    path: '/chauffeur/bus',
    component: () => import('@/views/BusChauffeurView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['chauffeur_bus'] },
  },
  {
    path: '/chauffeur/bus/map',
    component: () => import('@/views/BusMapView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre'] },
  },
  {
    path: '/chauffeur/bus/schedules',
    component: () => import('@/views/BusSchedulesView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre'] },
  },
  { path: '/bus/drive', redirect: '/chauffeur/bus' },
  { path: '/bus/map', redirect: '/chauffeur/bus/map' },
  { path: '/bus/schedules', redirect: '/chauffeur/bus/schedules' },
  // /compare route removed
  {
    path: '/admin',
    component: () => import('@/views/AdminDashboardView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['admin'] },
  },
  { path: '/admin/lignes', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/notifications', component: () => import('@/views/AdminNotificationsView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/incidents', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/chauffeurs', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/arrets', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/horaires', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/documents', component: () => import('@/views/AdminDocumentsView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/signalements', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/membres', component: () => import('@/views/AdminDashboardView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/statistiques', component: () => import('@/views/AdminStatistiquesView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/conducteur/vehicule', component: () => import('@/views/ConducteurVehiculeView.vue'), meta: { requiresAuth: true, allowedRoles: ['conducteur'] } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from) => {
  const authStore = useAuthStore()
  authStore.initFromStorage()
  const meta = to.meta as RouteMeta
  const role = authStore.role as AppRole | null

  // Require auth for routes that explicitly ask for it.
  if (meta.requiresAuth && !authStore.isAuthenticated) {
    return '/login'
  }

  // Guest-only routes redirect authenticated users to their landing page.
  if (meta.guest && authStore.isAuthenticated) {
    return role ? roleLandingPage[role] : '/trajets'
  }

  // If the route declares `allowedRoles`:
  // - Unauthenticated users must be sent to `/login` (avoid redirect loops).
  // - Authenticated users with the wrong role are redirected to their landing page with a 403 flag.
  if (meta.allowedRoles) {
    if (!authStore.isAuthenticated) {
      return '/login'
    }
    if (!role || !meta.allowedRoles.includes(role)) {
      return {
        path: role ? roleLandingPage[role] : '/trajets',
        query: { error: '403' },
      }
    }
  }

  return undefined
})

export default router

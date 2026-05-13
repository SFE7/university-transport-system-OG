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
  { path: '/login', component: () => import('@/views/LoginView.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('@/views/RegisterView.vue'), meta: { guest: true } },
  { path: '/login/chauffeur', component: () => import('@/views/LoginChauffeurView.vue'), meta: { guest: true } },
  { path: '/register/etudiant', component: () => import('@/views/RegisterEtudiantView.vue'), meta: { guest: true } },
  { path: '/register/professionnel', component: () => import('@/views/RegisterProfessionnelView.vue'), meta: { guest: true } },
  { path: '/register/conducteur', component: () => import('@/views/RegisterConducteurView.vue'), meta: { guest: true } },
  { path: '/trajets', component: () => import('@/views/TrajetsView.vue'), meta: { allowedRoles: ['membre', 'conducteur'] } },
  { path: '/trajets/:id', component: () => import('@/views/TrajetDetailView.vue'), meta: { allowedRoles: ['membre', 'conducteur'] } },
  {
    path: '/reservations',
    component: () => import('@/views/MyReservationsView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] },
  },
  {
    path: '/dashboard',
    component: () => import('@/views/ConducteurDashboardView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['conducteur'] },
  },
  { path: '/membres/:id/avis', component: () => import('@/views/ConducteurProfileView.vue') },
  { path: '/profil', component: () => import('@/views/ProfilView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] } },
  { path: '/membres/profil/edit', component: () => import('@/views/EditProfilView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] } },
  { path: '/profil/password', component: () => import('@/views/ChangePasswordView.vue'), meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur', 'chauffeur_bus'] } },
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
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] },
  },
  {
    path: '/chauffeur/bus/schedules',
    component: () => import('@/views/BusSchedulesView.vue'),
    meta: { requiresAuth: true, allowedRoles: ['membre', 'conducteur'] },
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
  { path: '/admin/documents', component: () => import('@/views/AdminDocumentsView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/admin/statistiques', component: () => import('@/views/AdminStatistiquesView.vue'), meta: { requiresAuth: true, allowedRoles: ['admin'] } },
  { path: '/conducteur/vehicule', component: () => import('@/views/ConducteurVehiculeView.vue'), meta: { requiresAuth: true, allowedRoles: ['conducteur'] } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  authStore.initFromStorage()
  const meta = to.meta as RouteMeta
  const role = authStore.role as AppRole | undefined

  if (meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }
  if (meta.guest && authStore.isAuthenticated) {
    return next(role ? roleLandingPage[role] : '/trajets')
  }
  if (meta.allowedRoles && (!role || !meta.allowedRoles.includes(role))) {
    return next({
      path: role ? roleLandingPage[role] : '/trajets',
      query: { error: '403' },
    })
  }
  next()
})

export default router

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const routes = [
  { path: '/', redirect: '/trajets' },
  { path: '/login', component: () => import('@/views/LoginView.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('@/views/RegisterView.vue'), meta: { guest: true } },
  { path: '/trajets', component: () => import('@/views/TrajetsView.vue') },
  { path: '/trajets/:id', component: () => import('@/views/TrajetDetailView.vue') },
  {
    path: '/reservations',
    component: () => import('@/views/MyReservationsView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/dashboard',
    component: () => import('@/views/ConducteurDashboardView.vue'),
    meta: { requiresAuth: true, conducteurOnly: true },
  },
  { path: '/membres/:id/avis', component: () => import('@/views/ConducteurProfileView.vue') },
  {
    path: '/history',
    component: () => import('@/views/TripHistoryView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/notifications',
    component: () => import('@/views/NotificationsView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/bus/map',
    component: () => import('@/views/BusMapView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/bus/drive',
    component: () => import('@/views/BusChauffeurView.vue'),
    meta: { requiresAuth: true },
  },
  { path: '/bus/schedules', component: () => import('@/views/BusSchedulesView.vue') },
  {
    path: '/compare',
    component: () => import('@/views/ComparisonView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin',
    component: () => import('@/views/AdminDashboardView.vue'),
    meta: { requiresAuth: true, adminOnly: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  authStore.initFromStorage()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }
  if (to.meta.conducteurOnly && !authStore.isConducteur) {
    return next('/trajets')
  }
  if (to.meta.adminOnly && String(authStore.membre?.role) !== 'admin') {
    return next('/trajets')
  }
  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/trajets')
  }
  next()
})

export default router

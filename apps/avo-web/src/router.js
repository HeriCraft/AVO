import { createRouter, createWebHistory } from 'vue-router'
import { usersRoutes } from './Users'
import { jobsRoutes } from './Jobs'
import { useAuthStore } from './Users/stores/useAuthStore'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  ...usersRoutes,
  ...jobsRoutes
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresAdmin && !authStore.isSuperAdmin) {
    next('/dashboard')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router

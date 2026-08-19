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
  ...jobsRoutes,
  {
    path: '/admin',
    component: () => import('./Shared/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'AdminDashboard', component: () => import('./Users/views/admin/AdminDashboardView.vue') },
      { path: 'users', name: 'AdminUsers', component: () => import('./Users/views/admin/UsersManagementView.vue') },
      { path: 'logs', name: 'AdminLogs', component: () => import('./Users/views/admin/ActivityLogsView.vue') },
      { path: 'settings', name: 'PlatformSettings', component: () => import('./Settings/views/PlatformSettingsView.vue') }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }

  if (authStore.isAuthenticated) {
    // SUPER_ADMIN Constraint: Must stay in /admin territory
    if (authStore.isSuperAdmin && !to.path.startsWith('/admin')) {
      return next('/admin/dashboard')
    }
    
    // Normal User Constraint: Cannot access /admin territory
    if (!authStore.isSuperAdmin && to.path.startsWith('/admin')) {
      return next('/dashboard')
    }
    
    // Authenticated users shouldn't see login page
    if (to.path === '/login') {
      return next(authStore.isSuperAdmin ? '/admin/dashboard' : '/dashboard')
    }
  }

  next()
})

export default router

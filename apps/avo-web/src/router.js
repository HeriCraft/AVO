import { createRouter, createWebHistory } from 'vue-router'
import { usersRoutes } from './Users'
import { candidatesRoutes } from './Candidates/routes'
import { useAuthStore } from './Users/stores/useAuthStore'

const routes = [
  {
    path: '/_public', // Dummy path, children use absolute paths
    component: () => import('./Shared/layouts/PublicLayout.vue'),
    meta: { requiresAuth: false },
    children: candidatesRoutes
  },
  {
    path: '/',
    component: () => import('./Shared/layouts/UserLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'UserDashboard', component: () => import('./Dashboard/views/UserDashboardView.vue') },
      { path: 'jobs', name: 'Jobs', component: () => import('./Jobs/views/JobsListView.vue') },
      { path: 'booking', name: 'Booking', component: () => import('./Booking/views/BookingCalendarView.vue') },
      { path: 'settings', name: 'Settings', component: () => import('./UserSettings/views/SettingsView.vue') }
    ]
  },
  ...usersRoutes,
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
    if (authStore.isSuperAdmin && !to.path.startsWith('/admin')) {
      return next('/admin/dashboard')
    }
    
    if (!authStore.isSuperAdmin && to.path.startsWith('/admin')) {
      return next('/dashboard')
    }
    
    if (to.path === '/login') {
      return next(authStore.isSuperAdmin ? '/admin/dashboard' : '/dashboard')
    }
  }

  next()
})

export default router

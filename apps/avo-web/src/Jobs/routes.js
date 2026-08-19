export const jobsRoutes = [
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('./views/JobsListView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('./views/JobsListView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  }
]

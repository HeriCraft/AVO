export const candidatesRoutes = [
  {
    path: '/jobs/:id',
    name: 'PublicJobView',
    component: () => import('./views/PublicJobView.vue'),
    meta: { layout: 'PublicLayout', requiresAuth: false }
  },
  {
    path: '/apply/:id/register',
    name: 'ApplyRegisterView',
    component: () => import('./views/ApplyView.vue'),
    meta: { layout: 'PublicLayout', requiresAuth: false }
  }
]

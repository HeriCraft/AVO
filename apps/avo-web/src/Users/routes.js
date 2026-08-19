import LoginView from './views/LoginView.vue';
import AdminDashboardView from './views/AdminDashboardView.vue';
import { useAuthStore } from './stores/useAuthStore';

const requireSuperAdmin = (to, from, next) => {
  const authStore = useAuthStore();
  if (authStore.isAuthenticated && authStore.isSuperAdmin) {
    next();
  } else {
    next('/login');
  }
};

export const usersRoutes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardView,
    beforeEnter: requireSuperAdmin
  }
];

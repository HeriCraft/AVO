import apiClient from '../../Shared/http/client'

export const login = async (credentials) => {
  const { data } = await apiClient.post('/auth/login', credentials);
  return data;
};

export const register = async (userData) => {
  const { data } = await apiClient.post('/auth/register', userData);
  return data;
};

export const logout = async () => {
  const { data } = await apiClient.post('/auth/logout');
  return data;
};

export const getAdminDashboard = async () => {
  const { data } = await apiClient.get('/admin/dashboard/metrics'); // Note: The route in API might be metrics, but let's check what was in the history: it was /admin/dashboard
  return data;
};

export const usersApi = {
  getUsers() {
    return apiClient.get('/admin/users')
  },
  
  toggleUserStatus(userId) {
    return apiClient.patch(`/admin/users/${userId}/toggle-status`)
  },

  createUser(userData) {
    return apiClient.post('/admin/users', userData)
  }
}

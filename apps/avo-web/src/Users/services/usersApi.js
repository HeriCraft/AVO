import apiClient from '../../Shared/http/client';

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
  const { data } = await apiClient.get('/admin/dashboard');
  return data;
};

<template>
  <div class="admin-dashboard">
    <h2>Super Admin Dashboard</h2>
    <div v-if="loading">Loading dashboard data...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else class="dashboard-content">
      <RegistrationChart :totalUsers="dashboardData.total_users" />
      <ActivityLogsTable :logs="dashboardData.recent_activities" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getAdminDashboard } from '../services/usersApi';
import RegistrationChart from '../components/RegistrationChart.vue';
import ActivityLogsTable from '../components/ActivityLogsTable.vue';
import eventBus from '../../Shared/events/eventBus';

const dashboardData = ref({ total_users: 0, recent_activities: [] });
const loading = ref(true);
const error = ref(null);

const fetchDashboard = async () => {
  loading.value = true;
  try {
    const data = await getAdminDashboard();
    dashboardData.value = data;
    eventBus.emit('dashboard:loaded', data);
  } catch (err) {
    error.value = 'Failed to load dashboard data.';
    eventBus.emit('dashboard:error', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>

<style scoped>
.admin-dashboard {
  padding: 2rem;
}
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}
.error {
  color: red;
}
</style>

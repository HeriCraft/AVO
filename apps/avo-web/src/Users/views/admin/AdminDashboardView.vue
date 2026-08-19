<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../../Shared/http/client'

const loading = ref(true)
const metrics = ref(null)

onMounted(async () => {
  try {
    const response = await apiClient.get('/admin/dashboard/metrics')
    metrics.value = response.data
  } catch (err) {
    console.error('Failed to load metrics', err)
  } finally {
    loading.value = false
  }
})

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(dateString))
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Dashboard Overview</h1>

    <div v-if="loading" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="metrics" class="space-y-6">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Users</div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ metrics.kpis.total_users }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Active Users</div>
          <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ metrics.kpis.active_users }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Roles</div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ metrics.role_distribution?.length || 0 }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Recent Logs</div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ metrics.recent_logs?.length || 0 }}</div>
        </div>
      </div>

      <!-- Recent Logs -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
          <h2 class="text-lg font-medium text-slate-900 dark:text-white">Recent Activity</h2>
        </div>
        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
          <li v-for="log in metrics.recent_logs" :key="log.id" class="px-6 py-4 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-900 dark:text-white">{{ log.user?.name || 'System' }}</p>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ log.description }}</p>
            </div>
            <div class="text-xs text-slate-400 dark:text-slate-500">
              {{ formatDate(log.created_at) }}
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

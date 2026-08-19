<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../../Shared/http/client'

const logs = ref([])
const loading = ref(true)

const fetchLogs = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/logs')
    logs.value = response.data.data // Pagination wrapper
  } catch (err) {
    console.error('Failed to load logs', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Activity Logs</h1>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-12 flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <table v-else class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP Address</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
          <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ new Date(log.created_at).toLocaleString() }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">{{ log.user?.name || 'System' }} ({{ log.user?.email || 'N/A' }})</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ log.description || log.action }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ log.ip_address || 'N/A' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

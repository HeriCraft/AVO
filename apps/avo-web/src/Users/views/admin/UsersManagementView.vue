<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../../Shared/http/client'

const users = ref([])
const loading = ref(true)

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/users')
    users.value = response.data
  } catch (err) {
    console.error('Failed to load users', err)
  } finally {
    loading.value = false
  }
}

const toggleStatus = async (user) => {
  try {
    const response = await apiClient.patch(`/admin/users/${user.id}/toggle-status`)
    const index = users.value.findIndex(u => u.id === user.id)
    if (index !== -1) {
      users.value[index].status = response.data.status
    }
  } catch (err) {
    console.error('Failed to toggle status', err)
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<template>
  <div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">User Management</h1>
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Add User</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-12 flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <table v-else class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
          <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ user.role }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <span :class="{
                'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400': user.status === 'ACTIVE',
                'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400': user.status === 'SUSPENDED',
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400': user.status === 'PENDING'
              }" class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                {{ user.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="toggleStatus(user)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                {{ user.status === 'ACTIVE' ? 'Suspend' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

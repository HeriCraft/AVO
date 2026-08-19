<script setup>
import { ref, onMounted } from 'vue'
import apiClient from '../../Shared/http/client'

const settings = ref([])
const loading = ref(true)

const fetchSettings = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/admin/settings')
    settings.value = response.data
  } catch (err) {
    console.error('Failed to load settings', err)
  } finally {
    loading.value = false
  }
}

const showModal = ref(false)
const isEditing = ref(false)
const currentSetting = ref({ key: '', value: '' })

const openModal = (setting = null) => {
  if (setting) {
    isEditing.value = true
    currentSetting.value = { key: setting.key, value: JSON.stringify(setting.value, null, 2) }
  } else {
    isEditing.value = false
    currentSetting.value = { key: '', value: '' }
  }
  showModal.value = true
}

const saveSetting = async () => {
  try {
    const payload = { ...currentSetting.value }
    try {
      payload.value = JSON.parse(payload.value)
    } catch(e) {
      // Keep as string if not valid JSON
    }

    if (isEditing.value) {
      await apiClient.put(`/admin/settings/${payload.key}`, { value: payload.value })
    } else {
      await apiClient.post('/admin/settings', payload)
    }
    showModal.value = false
    fetchSettings()
  } catch (err) {
    console.error('Failed to save setting', err)
  }
}

const deleteSetting = async (key) => {
  if (confirm('Are you sure you want to delete this setting?')) {
    try {
      await apiClient.delete(`/admin/settings/${key}`)
      fetchSettings()
    } catch (err) {
      console.error('Failed to delete setting', err)
    }
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Platform Settings</h1>
      <button @click="openModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Add Setting</button>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
      <div v-if="loading" class="p-12 flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <table v-else class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Key</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Value</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
          <tr v-for="setting in settings" :key="setting.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">{{ setting.key }}</td>
            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 max-w-md truncate font-mono">
              {{ typeof setting.value === 'object' ? JSON.stringify(setting.value) : setting.value }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
              <button @click="openModal(setting)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">Edit</button>
              <button @click="deleteSetting(setting.key)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ isEditing ? 'Edit Setting' : 'New Setting' }}</h3>
          
          <form @submit.prevent="saveSetting" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Key</label>
              <input type="text" v-model="currentSetting.key" :disabled="isEditing" required class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 disabled:opacity-50" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Value (JSON or String)</label>
              <textarea v-model="currentSetting.value" required rows="6" class="block w-full px-4 py-2 font-mono text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">Cancel</button>
              <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useJobStore } from '../stores/useJobStore'
import { useTheme } from '../../Shared/composables/useTheme'

const jobStore = useJobStore()
const { isDark, toggleTheme } = useTheme()

const showCreateModal = ref(false)
const newJob = ref({
  title: '',
  description: '',
  status: 'DRAFT'
})

onMounted(() => {
  jobStore.fetchJobs()
})

const handleCreateJob = async () => {
  const result = await jobStore.createJob(newJob.value)
  if (result.success) {
    showCreateModal.value = false
    newJob.value = { title: '', description: '', status: 'DRAFT' }
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date)
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <!-- Navbar / Header -->
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-blue-600 text-white flex items-center justify-center font-bold text-lg">A</div>
            <span class="text-xl font-semibold text-slate-900 dark:text-white">AVO</span>
          </div>
          <div class="flex items-center gap-4">
            <button @click="toggleTheme" class="p-2 rounded-full text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
              <span v-if="isDark">🌞</span>
              <span v-else>🌙</span>
            </button>
            <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 overflow-hidden">
              <img src="https://ui-avatars.com/api/?name=Admin&background=random" alt="Avatar" />
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Page Header -->
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Job Requisitions</h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage all open positions and drafts.</p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="showCreateModal = true" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Requisition
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        
        <div v-if="jobStore.loading && !jobStore.jobs.length" class="p-12 flex justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="jobStore.error" class="p-12 text-center text-red-500">
          {{ jobStore.error }}
        </div>

        <div v-else-if="jobStore.jobs.length === 0" class="p-12 text-center text-slate-500 dark:text-slate-400">
          No requisitions found. Create one to get started.
        </div>

        <table v-else class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
          <thead class="bg-slate-50 dark:bg-slate-950/50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Title</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recruiter</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Created</th>
              <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
            <tr v-for="job in jobStore.jobs" :key="job.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                {{ job.title }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="{
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400': job.status === 'DRAFT',
                  'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400': job.status === 'PUBLISHED',
                  'bg-slate-100 text-slate-800 dark:bg-slate-500/20 dark:text-slate-400': job.status === 'CLOSED'
                }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ job.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                {{ job.user?.name || 'System' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                {{ formatDate(job.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="#" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">Edit</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Create Modal Overlay -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="showCreateModal = false"></div>

        <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-800">
          <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-white mb-4">Create Requisition</h3>
          
          <form @submit.prevent="handleCreateJob" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
              <input type="text" v-model="newJob.title" required class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g. Senior Frontend Engineer" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
              <textarea v-model="newJob.description" required rows="4" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
              <select v-model="newJob.status" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="DRAFT">Draft</option>
                <option value="PUBLISHED">Published</option>
              </select>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</button>
              <button type="submit" :disabled="jobStore.loading" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ jobStore.loading ? 'Saving...' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

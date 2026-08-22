<script setup>
import { ref, onMounted } from 'vue'
import api from '../../Shared/http/client'

const applications = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await api.get('/applications')
    applications.value = response.data
  } catch (error) {
    console.error('Failed to fetch applications', error)
  } finally {
    loading.value = false
  }
})

const getScoreColor = (score) => {
  if (!score) return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300'
  const s = score.toUpperCase()
  if (s === 'GREEN') return 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400 border border-green-500/20'
  if (s === 'YELLOW') return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400 border border-yellow-500/20'
  if (s === 'RED') return 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-500/20'
  return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300'
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric', month: 'short', day: 'numeric'
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Applications</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review candidates who applied to your job postings.</p>
      </div>
    </div>

    <!-- Data Grid -->
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden relative">
      <div v-if="loading" class="p-12 flex justify-center">
        <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div v-else-if="applications.length === 0" class="p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">No applications</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">You don't have any applications yet.</p>
      </div>

      <table v-else class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidate</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Title</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">AI Score</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date Applied</th>
            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
          <tr v-for="app in applications" :key="app.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
            
            <!-- Candidate -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold">
                    {{ app.candidate?.firstname?.[0] || '?' }}{{ app.candidate?.lastname?.[0] || '' }}
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-slate-900 dark:text-white">
                    {{ app.candidate?.firstname || 'Unknown' }} {{ app.candidate?.lastname || '' }}
                  </div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">
                    {{ app.candidate?.email || 'No email' }}
                  </div>
                </div>
              </div>
            </td>
            
            <!-- Job Title -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-slate-900 dark:text-white">{{ app.job_post?.title || 'Unknown Job' }}</div>
            </td>
            
            <!-- AI Score -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getScoreColor(app.ai_score)]">
                {{ app.ai_score || 'PENDING' }}
              </span>
            </td>
            
            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-500/20">
                {{ app.status }}
              </span>
            </td>
            
            <!-- Date Applied -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
              {{ formatDate(app.created_at) }}
            </td>
            
            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors">
                View Details
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

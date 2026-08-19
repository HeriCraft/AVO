<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import apiClient from '../../Shared/http/client'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  PointElement,
  LineElement
)

const loading = ref(true)
const dashboardData = ref(null)

const activeFilter = ref('pending_actions')
const actionCenterData = ref([])
const actionCenterType = ref('none')
const actionCenterLoading = ref(false)

const fetchDetails = async (filter) => {
  actionCenterLoading.value = true
  try {
    const response = await apiClient.get(`/user/dashboard/details?filter=${filter}`)
    actionCenterData.value = response.data.data
    actionCenterType.value = response.data.type
  } catch (error) {
    console.error('Failed to load details data', error)
    actionCenterData.value = []
    actionCenterType.value = 'none'
  } finally {
    actionCenterLoading.value = false
  }
}

onMounted(async () => {
  try {
    const response = await apiClient.get('/user/dashboard/metrics')
    dashboardData.value = response.data
    await fetchDetails(activeFilter.value)
  } catch (error) {
    console.error('Failed to load dashboard data', error)
  } finally {
    loading.value = false
  }
})

watch(activeFilter, (newFilter) => {
  fetchDetails(newFilter)
})

const setActiveFilter = (filter) => {
  activeFilter.value = filter
}

const aiScoreChartData = computed(() => {
  if (!dashboardData.value) return { labels: [], datasets: [] }
  const scores = dashboardData.value.charts.ai_scores
  return {
    labels: ['Green', 'Yellow', 'Red'],
    datasets: [{
      data: [scores.GREEN, scores.YELLOW, scores.RED],
      backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
      borderWidth: 0
    }]
  }
})

const funnelChartData = computed(() => {
  if (!dashboardData.value) return { labels: [], datasets: [] }
  const funnel = dashboardData.value.charts.funnel
  return {
    labels: ['New', 'Pending Review', 'Shortlisted', 'Hired'],
    datasets: [{
      label: 'Candidates',
      data: [funnel.NEW, funnel.PENDING_HUMAN_REVIEW, funnel.SHORTLISTED, funnel.HIRED],
      backgroundColor: '#3b82f6',
      borderRadius: 4
    }]
  }
})

const acquisitionChartData = computed(() => {
  if (!dashboardData.value) return { labels: [], datasets: [] }
  const acq = dashboardData.value.charts.acquisition
  return {
    labels: Object.keys(acq),
    datasets: [{
      label: 'New Candidates',
      data: Object.values(acq),
      borderColor: '#8b5cf6',
      backgroundColor: '#8b5cf6',
      tension: 0.3
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { labels: { color: '#94a3b8' } }
  },
  scales: {
    x: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } },
    y: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } }
  }
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  cutout: '70%'
}
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Recruiter Dashboard</h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Real-time insights on your recruitment pipeline.</p>
    </div>

    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="dashboardData" class="space-y-6">
      
      <!-- KPI Row (Interactive) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <button 
          @click="setActiveFilter('pending_actions')"
          :class="[
            'bg-white dark:bg-slate-900 rounded-xl p-5 border shadow-sm relative overflow-hidden group text-left w-full transition-all duration-200 hover:-translate-y-1 hover:shadow-md',
            activeFilter === 'pending_actions' ? 'border-amber-500 dark:border-amber-500 ring-1 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800'
          ]"
        >
          <div :class="['absolute inset-x-0 bottom-0 h-1 bg-amber-500 transition-transform origin-left', activeFilter === 'pending_actions' ? 'scale-x-100' : 'transform scale-x-0 group-hover:scale-x-100']"></div>
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Pending Actions</p>
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ dashboardData.kpis.pending_action_count }}</h3>
            </div>
            <div class="p-2 bg-amber-50 dark:bg-amber-500/10 rounded-lg">
              <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
          </div>
        </button>

        <button 
          @click="setActiveFilter('ai_interviews')"
          :class="[
            'bg-white dark:bg-slate-900 rounded-xl p-5 border shadow-sm relative overflow-hidden group text-left w-full transition-all duration-200 hover:-translate-y-1 hover:shadow-md',
            activeFilter === 'ai_interviews' ? 'border-blue-500 dark:border-blue-500 ring-1 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800'
          ]"
        >
          <div :class="['absolute inset-x-0 bottom-0 h-1 bg-blue-500 transition-transform origin-left', activeFilter === 'ai_interviews' ? 'scale-x-100' : 'transform scale-x-0 group-hover:scale-x-100']"></div>
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">AI Interviews (30d)</p>
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ dashboardData.kpis.ai_interviews_30d }}</h3>
            </div>
            <div class="p-2 bg-blue-50 dark:bg-blue-500/10 rounded-lg">
              <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
          </div>
        </button>

        <button 
          @click="setActiveFilter('active_jobs')"
          :class="[
            'bg-white dark:bg-slate-900 rounded-xl p-5 border shadow-sm relative overflow-hidden group text-left w-full transition-all duration-200 hover:-translate-y-1 hover:shadow-md',
            activeFilter === 'active_jobs' ? 'border-indigo-500 dark:border-indigo-500 ring-1 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-800'
          ]"
        >
          <div :class="['absolute inset-x-0 bottom-0 h-1 bg-indigo-500 transition-transform origin-left', activeFilter === 'active_jobs' ? 'scale-x-100' : 'transform scale-x-0 group-hover:scale-x-100']"></div>
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Active Jobs</p>
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ dashboardData.kpis.active_jobs_count }}</h3>
            </div>
            <div class="p-2 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg">
              <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
          </div>
        </button>

        <div class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
          <div class="absolute inset-x-0 bottom-0 h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Conversion Rate</p>
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ dashboardData.kpis.conversion_rate }}%</h3>
            </div>
            <div class="p-2 bg-emerald-50 dark:bg-emerald-500/10 rounded-lg">
              <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 flex flex-col">
          <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">AI Score Distribution</h3>
          <div class="h-48 relative mb-6">
            <Doughnut :data="aiScoreChartData" :options="doughnutOptions" />
          </div>
          
          <div class="space-y-4 mt-auto">
            <!-- Green -->
            <div class="flex items-start">
              <span class="w-3 h-3 rounded-full bg-emerald-500 mt-1 flex-shrink-0"></span>
              <div class="ml-3 flex-1">
                <div class="flex justify-between items-center">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">Highly Recommended</p>
                  <span class="text-sm font-bold text-slate-900 dark:text-white">{{ dashboardData.charts.ai_scores.GREEN || 0 }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Top tier candidates, excellent match</p>
              </div>
            </div>
            <!-- Yellow -->
            <div class="flex items-start">
              <span class="w-3 h-3 rounded-full bg-amber-500 mt-1 flex-shrink-0"></span>
              <div class="ml-3 flex-1">
                <div class="flex justify-between items-center">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">Needs Review</p>
                  <span class="text-sm font-bold text-slate-900 dark:text-white">{{ dashboardData.charts.ai_scores.YELLOW || 0 }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Average fit, human validation required</p>
              </div>
            </div>
            <!-- Red -->
            <div class="flex items-start">
              <span class="w-3 h-3 rounded-full bg-red-500 mt-1 flex-shrink-0"></span>
              <div class="ml-3 flex-1">
                <div class="flex justify-between items-center">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">Not Recommended</p>
                  <span class="text-sm font-bold text-slate-900 dark:text-white">{{ dashboardData.charts.ai_scores.RED || 0 }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Poor fit based on AI evaluation</p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
          <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Recruitment Funnel</h3>
          <div class="h-64 relative">
            <Bar :data="funnelChartData" :options="chartOptions" />
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
          <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Acquisition (14 Days)</h3>
          <div class="h-64 relative">
            <Line :data="acquisitionChartData" :options="chartOptions" />
          </div>
        </div>

      </div>

      <!-- Action Center (Contextual Table) -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            Action Center
            <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
              {{ activeFilter === 'pending_actions' ? 'Pending Review' : (activeFilter === 'active_jobs' ? 'Active Jobs' : 'Recent Interviews') }}
            </span>
          </h3>
        </div>

        <div v-if="actionCenterLoading" class="p-12 flex justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else class="overflow-x-auto">
          <!-- Candidates Table -->
          <table v-if="actionCenterType === 'candidates'" class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-950/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidate Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Post</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Applied Date</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
              <tr v-for="item in actionCenterData" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">{{ item.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ item.job_post?.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ new Date(item.created_at).toLocaleDateString() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">Review</button>
                </td>
              </tr>
              <tr v-if="!actionCenterData.length">
                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">No candidates pending review.</td>
              </tr>
            </tbody>
          </table>

          <!-- Jobs Table -->
          <table v-else-if="actionCenterType === 'jobs'" class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-950/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Title</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Candidates</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
              <tr v-for="item in actionCenterData" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">{{ item.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                  <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ item.candidates_count }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400">{{ item.status }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">View Pipeline</button>
                </td>
              </tr>
              <tr v-if="!actionCenterData.length">
                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">No active jobs found.</td>
              </tr>
            </tbody>
          </table>

          <!-- Interviews Table -->
          <table v-else-if="actionCenterType === 'interviews'" class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-950/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidate</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Post</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Scheduled At</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
              <tr v-for="item in actionCenterData" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">{{ item.candidate?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ item.candidate?.job_post?.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                  {{ new Date(item.scheduled_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">{{ item.status }}</span>
                </td>
              </tr>
              <tr v-if="!actionCenterData.length">
                <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">No interviews in the last 30 days.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

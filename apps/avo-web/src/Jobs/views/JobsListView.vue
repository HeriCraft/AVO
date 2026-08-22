<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue'
import { useJobStore } from '../stores/useJobStore'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

const jobStore = useJobStore()

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const showImageModal = ref(false)
const editingJobId = ref(null)
const selectedJob = ref(null)

const formData = ref({
  title: '',
  description: '',
  status: 'DRAFT'
})
const coverImageFile = ref(null)
const fileInput = ref(null)
let pollInterval = null

onMounted(() => {
  jobStore.fetchJobs()
  // Poll individually for jobs with pending tags
  pollInterval = setInterval(() => {
    const pendingJobs = jobStore.jobs.filter(j => j.tags === null)
    pendingJobs.forEach(job => {
      jobStore.fetchJobTags(job.id)
    })
  }, 3000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

const getImageUrl = (path) => {
  if (!path) return ''
  // Return absolute URL pointing directly to the Laravel API domain
  return `http://api.avo.local/storage/${path}`
}

const handleFileChange = (e) => {
  if (e.target.files.length > 0) {
    coverImageFile.value = e.target.files[0]
  }
}

const openCreate = () => {
  editingJobId.value = null
  formData.value = { title: '', description: '', status: 'DRAFT' }
  coverImageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  showCreateModal.value = true
}

const openEdit = (job) => {
  editingJobId.value = job.id
  formData.value = { title: job.title, description: job.description, status: job.status }
  coverImageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  showEditModal.value = true
}

const openView = (job) => {
  selectedJob.value = job
  showViewModal.value = true
}

const formattedSelectedJobDescription = computed(() => {
  if (!selectedJob.value || !selectedJob.value.description) return ''
  marked.setOptions({ breaks: true })
  const html = marked.parse(selectedJob.value.description)
  return DOMPurify.sanitize(html)
})

const handleSubmit = async () => {
  const payload = new FormData()
  payload.append('title', formData.value.title)
  payload.append('description', formData.value.description)
  payload.append('status', formData.value.status)
  if (coverImageFile.value) {
    payload.append('cover_image', coverImageFile.value)
  }

  if (editingJobId.value) {
    const result = await jobStore.updateJob(editingJobId.value, payload)
    if (result.success) showEditModal.value = false
  } else {
    const result = await jobStore.createJob(payload)
    if (result.success) showCreateModal.value = false
  }
}

const handleDelete = async (id) => {
  if (confirm('Are you sure you want to delete this job?')) {
    await jobStore.deleteJob(id)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(date)
}
</script>

<template>
  <div class="h-full">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Job Requisitions</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage all open positions and drafts.</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <button @click="openCreate" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
          <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Requisition
        </button>
      </div>
    </div>

    <!-- Data Display -->
    <div v-if="jobStore.loading && !jobStore.jobs.length" class="p-12 flex justify-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    
    <div v-else-if="jobStore.error" class="p-12 text-center text-red-500 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
      {{ jobStore.error }}
    </div>
    
    <div v-else-if="jobStore.jobs.length === 0" class="p-12 text-center text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
      No requisitions found. Create one to get started.
    </div>
    
    <!-- Card Grid Layout -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="job in jobStore.jobs" :key="job.id" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col hover:shadow-md transition-shadow cursor-pointer group" @click="openView(job)">
        
        <!-- Header / Cover Image -->
        <div v-if="job.cover_image_path" class="h-32 w-full relative">
          <img :src="getImageUrl(job.cover_image_path)" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Cover Image" />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
        </div>
        <div v-else class="h-32 w-full bg-gradient-to-r from-blue-500 to-indigo-600"></div>
        
        <!-- Body -->
        <div class="p-5 flex-1 flex flex-col">
          <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-tight truncate mr-2">{{ job.title }}</h3>
            <span :class="{
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400': job.status === 'DRAFT',
              'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400': job.status === 'PUBLISHED',
              'bg-slate-100 text-slate-800 dark:bg-slate-500/20 dark:text-slate-400': job.status === 'CLOSED'
            }" class="px-2.5 py-0.5 rounded-full text-xs font-medium shrink-0">
              {{ job.status }}
            </span>
          </div>
          
          <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Created {{ formatDate(job.created_at) }}</p>
          
          <!-- AI Tags -->
          <div class="mt-auto mb-2">
            <template v-if="job.tags === null">
              <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50 rounded-lg animate-pulse">
                <svg class="w-4 h-4 text-indigo-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Generating AI Tags...</span>
              </div>
            </template>
            <template v-else>
              <div class="flex flex-wrap gap-2">
                <span v-for="tag in job.tags" :key="tag" class="px-2 py-1 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-md border border-slate-200 dark:border-slate-700">
                  {{ tag }}
                </span>
                <span v-if="job.tags.length === 0" class="text-xs text-slate-400 italic">No tags generated</span>
              </div>
            </template>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between" @click.stop>
          <div>
            <a v-if="job.status === 'PUBLISHED'" :href="`/jobs/${job.id}`" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors" title="Lien de candidature public">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
              Lien Public
            </a>
          </div>
          <div class="flex items-center gap-3">
            <button @click="openEdit(job)" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Edit</button>
            <button @click="handleDelete(job.id)" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Job Modal Overlay -->
    <div v-if="showViewModal && selectedJob" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="showViewModal = false"></div>

        <div class="relative inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-800">
          
          <div v-if="selectedJob.cover_image_path" class="w-full h-48 sm:h-64 relative group cursor-pointer" @click="showImageModal = true">
            <img :src="getImageUrl(selectedJob.cover_image_path)" class="w-full h-full object-cover" alt="Cover Image" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center">
              <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
              </svg>
            </div>
          </div>
          <div v-else class="w-full h-32 sm:h-48 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
          
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ selectedJob.title }}</h3>
              <span :class="{
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-400': selectedJob.status === 'DRAFT',
                'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400': selectedJob.status === 'PUBLISHED',
                'bg-slate-100 text-slate-800 dark:bg-slate-500/20 dark:text-slate-400': selectedJob.status === 'CLOSED'
              }" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shrink-0 mt-1">
                {{ selectedJob.status }}
              </span>
            </div>
            
            <div class="flex flex-wrap gap-2 mb-6">
              <span v-for="tag in selectedJob.tags || []" :key="tag" class="px-2.5 py-1 text-sm font-medium bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 rounded-lg border border-blue-100 dark:border-blue-500/20">
                {{ tag }}
              </span>
            </div>
            
            <div class="prose prose-slate dark:prose-invert max-w-none prose-p:leading-relaxed prose-headings:font-bold prose-a:text-indigo-600 dark:prose-a:text-indigo-400">
              <h4 class="text-lg font-semibold mb-2 text-slate-900 dark:text-white">Job Description</h4>
              <div class="text-slate-600 dark:text-slate-300" v-html="formattedSelectedJobDescription"></div>
            </div>
          </div>
          
          <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
            <div>
              <a v-if="selectedJob.status === 'PUBLISHED'" :href="`/jobs/${selectedJob.id}`" target="_blank" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors" title="Lien de candidature public">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Ouvrir la page publique
              </a>
            </div>
            <div class="flex gap-3">
              <button @click="showViewModal = false" class="px-5 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Close</button>
              <button @click="showViewModal = false; openEdit(selectedJob)" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Edit Requisition
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Full-size Image Lightbox Overlay -->
    <div v-if="showImageModal && selectedJob && selectedJob.cover_image_path" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/95 backdrop-blur-md p-4 sm:p-8" @click="showImageModal = false">
      <button @click="showImageModal = false" class="absolute top-4 right-4 sm:top-8 sm:right-8 p-2 text-white/70 hover:text-white bg-black/40 hover:bg-black/60 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-white/50">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <img :src="getImageUrl(selectedJob.cover_image_path)" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" @click.stop alt="Full size cover" />
    </div>

    <!-- Create/Edit Modal Overlay -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/75 backdrop-blur-sm" @click="showCreateModal = false; showEditModal = false"></div>

        <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-slate-200 dark:border-slate-800">
          <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-white mb-4">
            {{ editingJobId ? 'Edit Requisition' : 'Create Requisition' }}
          </h3>
          
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
              <input type="text" v-model="formData.title" required class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" placeholder="e.g. Senior Frontend Engineer" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
              <textarea v-model="formData.description" required rows="4" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
              <select v-model="formData.status" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="DRAFT">Draft</option>
                <option value="PUBLISHED">Published</option>
                <option value="CLOSED">Closed</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cover Image (Optional)</label>
              <input type="file" ref="fileInput" @change="handleFileChange" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 transition-colors" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button type="button" @click="showCreateModal = false; showEditModal = false" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</button>
              <button type="submit" :disabled="jobStore.loading" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ jobStore.loading ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

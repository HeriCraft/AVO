<template>
  <div class="max-w-4xl mx-auto w-full">
    
    <!-- Loading State -->
    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-64 bg-slate-200 dark:bg-slate-800 rounded-xl w-full"></div>
      <div class="h-10 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
      <div class="flex gap-2">
        <div class="h-6 w-20 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
        <div class="h-6 w-20 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
      </div>
      <div class="space-y-3 pt-6">
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
        <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-4/6"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-12 text-center shadow-sm">
      <div class="mx-auto w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Cette offre n'est plus disponible</h2>
      <p class="text-slate-500 dark:text-slate-400 mb-8 max-w-md mx-auto">
        Le poste que vous recherchez a peut-être été pourvu, fermé, ou l'URL est incorrecte.
      </p>
      <button @click="$router.push('/')" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
        Retour à l'accueil
      </button>
    </div>

    <!-- Content State -->
    <div v-else-if="job" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
      
      <!-- Cover Image -->
      <div v-if="job.cover_image_path" class="w-full h-48 sm:h-64 md:h-80 relative bg-slate-100 dark:bg-slate-800">
        <img 
          :src="getImageUrl(job.cover_image_path)" 
          alt="Cover" 
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      </div>

      <div class="p-6 md:p-10">
        <!-- Header area -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
          <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-4">
              {{ job.title }}
            </h1>
            
            <div class="flex flex-wrap gap-2" v-if="job.tags && job.tags.length">
              <span 
                v-for="(tag, index) in job.tags" 
                :key="index"
                class="px-3 py-1 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-sm font-semibold rounded-full border border-indigo-100 dark:border-indigo-500/20"
              >
                {{ tag }}
              </span>
            </div>
          </div>
          
          <!-- Desktop Apply CTA -->
          <div class="hidden md:block shrink-0">
            <button 
              @click="apply" 
              class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-lg rounded-xl font-bold transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              Postuler
            </button>
          </div>
        </div>

        <hr class="border-slate-200 dark:border-slate-800 mb-8" />

        <!-- Job Description -->
        <div class="prose prose-slate dark:prose-invert max-w-none prose-p:leading-relaxed prose-headings:font-bold prose-a:text-indigo-600 dark:prose-a:text-indigo-400">
          <div class="font-sans text-slate-700 dark:text-slate-300" v-html="formattedDescription"></div>
        </div>

        <!-- Mobile Apply CTA (Sticky bottom) -->
        <div class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white/90 dark:bg-slate-950/90 backdrop-blur border-t border-slate-200 dark:border-slate-800 z-40">
          <button 
            @click="apply" 
            class="w-full px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-lg rounded-xl font-bold transition-all shadow-lg"
          >
            Postuler
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

const route = useRoute()
const router = useRouter()

const job = ref(null)
const loading = ref(true)
const error = ref(false)

const getImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `http://api.avo.local/storage/${path.replace('public/', '')}`
}

const apply = () => {
  router.push(`/apply/${job.value.id}/register`)
}

const formattedDescription = computed(() => {
  if (!job.value || !job.value.description) return ''
  // Configure marked if needed (e.g., breaks: true for newlines)
  marked.setOptions({ breaks: true })
  const html = marked.parse(job.value.description)
  return DOMPurify.sanitize(html)
})

onMounted(async () => {
  try {
    const response = await axios.get(`/api/public/jobs/${route.params.id}`)
    job.value = response.data
  } catch (e) {
    error.value = true
  } finally {
    loading.value = false
  }
})
</script>

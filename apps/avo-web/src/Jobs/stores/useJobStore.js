import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '../../Shared/http/client'

export const useJobStore = defineStore('jobs', () => {
  const jobs = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchJobs() {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/jobs')
      jobs.value = response.data
    } catch (err) {
      error.value = 'Failed to load jobs'
    } finally {
      loading.value = false
    }
  }

  async function createJob(jobData) {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.post('/jobs', jobData)
      jobs.value.unshift(response.data)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create job'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    jobs,
    loading,
    error,
    fetchJobs,
    createJob
  }
})

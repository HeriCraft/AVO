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
      const response = await apiClient.post('/jobs', jobData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      jobs.value.unshift(response.data)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create job'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function updateJob(id, jobData) {
    loading.value = true
    error.value = null
    try {
      // Laravel requires POST with _method=PUT for multipart/form-data
      jobData.append('_method', 'PUT')
      const response = await apiClient.post(`/jobs/${id}`, jobData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      const index = jobs.value.findIndex(j => j.id === id)
      if (index !== -1) {
        jobs.value[index] = response.data
      }
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update job'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function fetchJobTags(id) {
    try {
      const response = await apiClient.get(`/jobs/${id}/tags`)
      const tags = response.data.tags
      if (tags !== null) {
        const index = jobs.value.findIndex(j => j.id === id)
        if (index !== -1) {
          jobs.value[index].tags = tags
        }
        return tags
      }
      return null
    } catch (err) {
      console.error('Failed to fetch tags for job', id)
      return null
    }
  }

  async function deleteJob(id) {
    loading.value = true
    error.value = null
    try {
      await apiClient.delete(`/jobs/${id}`)
      jobs.value = jobs.value.filter(j => j.id !== id)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete job'
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
    fetchJobTags,
    createJob,
    updateJob,
    deleteJob
  }
})

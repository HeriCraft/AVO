import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiClient from '../../Shared/http/client'
import { useAuthStore } from '../../Users/stores/useAuthStore'

export const useSettingsStore = defineStore('userSettings', () => {
  const loading = ref(false)
  const saving = ref(false)
  const error = ref(null)
  
  const profile = ref({
    first_name: '',
    last_name: '',
    company: '',
    company_role: ''
  })
  
  const aiConfig = ref({
    ai_voice_tone: 'Professional',
    ai_interview_language: 'English',
    ai_strictness_level: 'Medium'
  })

  async function fetchSettings() {
    loading.value = true
    try {
      const authStore = useAuthStore()
      if (authStore.user) {
        const nameParts = (authStore.user.name || '').split(' ')
        profile.value.first_name = nameParts[0] || ''
        profile.value.last_name = nameParts.slice(1).join(' ') || ''
        profile.value.company = authStore.user.company || ''
        profile.value.company_role = authStore.user.company_role || ''
      }
      const response = await apiClient.get('/user/settings')
      if (response.data) {
        aiConfig.value = {
          ai_voice_tone: response.data.ai_voice_tone || 'Professional',
          ai_interview_language: response.data.ai_interview_language || 'English',
          ai_strictness_level: response.data.ai_strictness_level || 'Medium'
        }
      }
    } catch (err) {
      error.value = 'Failed to load settings'
    } finally {
      loading.value = false
    }
  }

  async function saveSettings() {
    saving.value = true
    try {
      const payload = { ...profile.value, ...aiConfig.value }
      const response = await apiClient.put('/user/settings', payload)
      
      const authStore = useAuthStore()
      if (response.data.user) {
        authStore.setSession(authStore.token, response.data.user)
      }
      return { success: true }
    } catch (err) {
      error.value = 'Failed to save settings'
      return { success: false }
    } finally {
      saving.value = false
    }
  }

  return {
    profile,
    aiConfig,
    loading,
    saving,
    error,
    fetchSettings,
    saveSettings
  }
})

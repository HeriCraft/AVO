<script setup>
import { onMounted, ref } from 'vue'
import { useSettingsStore } from '../stores/useSettingsStore'

const settingsStore = useSettingsStore()
const activeTab = ref('profile')

onMounted(() => {
  settingsStore.fetchSettings()
})

const handleSave = async () => {
  const result = await settingsStore.saveSettings()
  if (result.success) {
    alert('Settings saved successfully!')
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto h-full flex flex-col">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Account Settings</h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage your profile and configure CALL-E AI parameters.</p>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button 
          @click="activeTab = 'profile'"
          :class="[
            activeTab === 'profile' 
              ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-700',
            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          Profile & General
        </button>
        <button 
          @click="activeTab = 'ai'"
          :class="[
            activeTab === 'ai' 
              ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
              : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:border-slate-700',
            'whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors'
          ]"
        >
          CALL-E AI Config
        </button>
      </nav>
    </div>

    <!-- Form Content -->
    <div class="flex-1 overflow-y-auto">
      <form @submit.prevent="handleSave" class="space-y-6">
        
        <!-- Profile Tab -->
        <div v-show="activeTab === 'profile'" class="space-y-6 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name</label>
              <input type="text" v-model="settingsStore.profile.first_name" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name</label>
              <input type="text" v-model="settingsStore.profile.last_name" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Company</label>
              <input type="text" v-model="settingsStore.profile.company" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Company Role</label>
              <input type="text" v-model="settingsStore.profile.company_role" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
        </div>

        <!-- AI Tab -->
        <div v-show="activeTab === 'ai'" class="space-y-6 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="grid grid-cols-1 gap-y-6">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">AI Voice Tone</label>
              <select v-model="settingsStore.aiConfig.ai_voice_tone" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="Professional">Professional & Corporate</option>
                <option value="Friendly">Friendly & Welcoming</option>
                <option value="Strict">Strict & Direct</option>
              </select>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Controls the personality of CALL-E during phone screens.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Interview Language</label>
              <select v-model="settingsStore.aiConfig.ai_interview_language" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="English">English</option>
                <option value="French">French</option>
                <option value="Spanish">Spanish</option>
                <option value="German">German</option>
              </select>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">The primary language CALL-E will use when calling candidates.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Strictness Level</label>
              <select v-model="settingsStore.aiConfig.ai_strictness_level" class="block w-full px-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="Low">Low (Lenient)</option>
                <option value="Medium">Medium (Balanced)</option>
                <option value="High">High (Challenging)</option>
              </select>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Determines how hard CALL-E will probe into technical answers.</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <button type="submit" :disabled="settingsStore.saving" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50">
            {{ settingsStore.saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

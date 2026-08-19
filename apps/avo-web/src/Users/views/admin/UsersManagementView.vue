<script setup>
import { ref, onMounted } from 'vue'
import { usersApi } from '../../services/usersApi'

const users = ref([])
const loading = ref(true)

// Modal State
const showModal = ref(false)
const submitting = ref(false)
const formData = ref({
  username: '',
  email: '',
  password: '',
  role: 'USER',
  first_name: '',
  last_name: '',
  company: '',
  company_role: ''
})
const validationErrors = ref({})
const generalError = ref('')

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await usersApi.getUsers()
    users.value = response.data
  } catch (err) {
    console.error('Failed to load users', err)
  } finally {
    loading.value = false
  }
}

const toggleStatus = async (user) => {
  try {
    const response = await usersApi.toggleUserStatus(user.id)
    const index = users.value.findIndex(u => u.id === user.id)
    if (index !== -1) {
      users.value[index].status = response.data.status
    }
  } catch (err) {
    console.error('Failed to toggle status', err)
  }
}

const openModal = () => {
  formData.value = {
    username: '',
    email: '',
    password: '',
    role: 'USER',
    first_name: '',
    last_name: '',
    company: '',
    company_role: ''
  }
  validationErrors.value = {}
  generalError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const createUser = async () => {
  submitting.value = true
  validationErrors.value = {}
  generalError.value = ''

  try {
    const response = await usersApi.createUser(formData.value)
    users.value.unshift(response.data) // Prepend new user
    closeModal()
  } catch (err) {
    if (err.response?.status === 422) {
      validationErrors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'An unexpected error occurred.'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">User Management</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage all users, update their statuses, and provision new accounts.</p>
      </div>
      <button @click="openModal" class="mt-4 sm:mt-0 flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-semibold text-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Provision User
      </button>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-[#0B1120] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative z-10">
      <div v-if="loading" class="p-12 flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      
      <div class="overflow-x-auto" v-else>
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800/50">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Name / Username</th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Contact</th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Role</th>
              <th class="px-6 py-3 text-left text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</th>
              <th class="px-6 py-3 text-right text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800/50">
            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ user.name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">@{{ user.username || `user-${user.id}` }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-700 dark:text-slate-300">{{ user.email }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" v-if="user.company">{{ user.company_role }} at {{ user.company }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400 border border-green-200 dark:border-green-500/20': user.status === 'ACTIVE',
                  'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400 border border-red-200 dark:border-red-500/20': user.status === 'SUSPENDED',
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-500/20': user.status === 'PENDING'
                }" class="px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase">
                  {{ user.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="toggleStatus(user)" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-semibold transition-colors">
                  {{ user.status === 'ACTIVE' ? 'Suspend' : 'Activate' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Slide-over / Modal for User Creation -->
    <div v-if="showModal" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

      <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
            <div class="pointer-events-auto w-screen max-w-lg transform transition-all shadow-2xl">
              
              <form @submit.prevent="createUser" class="flex h-full flex-col divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-[#0B1120] shadow-xl border-l border-slate-200 dark:border-slate-800">
                
                <div class="h-0 flex-1 overflow-y-auto">
                  <div class="bg-blue-600 dark:bg-slate-900 px-4 py-6 sm:px-6 relative overflow-hidden border-b border-transparent dark:border-slate-800">
                    <!-- Decor -->
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-white opacity-5 dark:bg-blue-500 dark:opacity-10 rounded-full blur-2xl"></div>
                    
                    <div class="flex items-center justify-between relative z-10">
                      <h2 class="text-xl font-bold text-white" id="slide-over-title">Provision New User</h2>
                      <button type="button" @click="closeModal" class="text-blue-200 hover:text-white dark:text-slate-400 dark:hover:text-white focus:outline-none transition-colors">
                        <span class="sr-only">Close panel</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                    <div class="mt-1">
                      <p class="text-sm text-blue-100 dark:text-slate-400 relative z-10">Fill in the information below to create a new platform user.</p>
                    </div>
                  </div>

                  <div class="flex flex-1 flex-col justify-between">
                    <div class="divide-y divide-slate-200 dark:divide-slate-800/50 px-4 sm:px-6">
                      
                      <!-- General Error -->
                      <div v-if="generalError" class="mt-4 p-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-sm text-red-600 dark:text-red-400">
                        {{ generalError }}
                      </div>

                      <!-- Section 1: Mandatory Information -->
                      <div class="space-y-6 pb-6 pt-6">
                        <div>
                          <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Mandatory Credentials</h3>
                          
                          <div class="grid grid-cols-1 gap-y-5">
                            <!-- Username -->
                            <div>
                              <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Username <span class="text-red-500">*</span></label>
                              <div class="mt-1">
                                <input type="text" id="username" v-model="formData.username" required
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border transition-colors"
                                  :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': validationErrors.username}" />
                                <p v-if="validationErrors.username" class="mt-1 text-xs text-red-500 font-medium">{{ validationErrors.username[0] }}</p>
                              </div>
                            </div>

                            <!-- Email -->
                            <div>
                              <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Address <span class="text-red-500">*</span></label>
                              <div class="mt-1">
                                <input type="email" id="email" v-model="formData.email" required
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border transition-colors"
                                  :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': validationErrors.email}" />
                                <p v-if="validationErrors.email" class="mt-1 text-xs text-red-500 font-medium">{{ validationErrors.email[0] }}</p>
                              </div>
                            </div>

                            <!-- Password -->
                            <div>
                              <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Initial Password <span class="text-red-500">*</span></label>
                              <div class="mt-1">
                                <input type="password" id="password" v-model="formData.password" required
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border transition-colors"
                                  :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': validationErrors.password}" />
                                <p v-if="validationErrors.password" class="mt-1 text-xs text-red-500 font-medium">{{ validationErrors.password[0] }}</p>
                              </div>
                            </div>

                            <!-- Role (Locked) -->
                            <div>
                              <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300">System Role <span class="text-red-500">*</span></label>
                              <div class="mt-1">
                                <input type="text" id="role" v-model="formData.role" readonly disabled
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 px-3 py-2 border cursor-not-allowed sm:text-sm font-bold" />
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Role is locked to standard USER for manual provisioning.</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Section 2: Optional Information -->
                      <div class="space-y-6 pb-6 pt-6">
                        <div>
                          <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Optional Profile</h3>
                          
                          <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- First Name -->
                            <div>
                              <label for="first_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">First Name</label>
                              <div class="mt-1">
                                <input type="text" id="first_name" v-model="formData.first_name"
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border" />
                              </div>
                            </div>
                            
                            <!-- Last Name -->
                            <div>
                              <label for="last_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Last Name</label>
                              <div class="mt-1">
                                <input type="text" id="last_name" v-model="formData.last_name"
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border" />
                              </div>
                            </div>
                          </div>

                          <div class="grid grid-cols-1 gap-y-5">
                            <!-- Company -->
                            <div>
                              <label for="company" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Company</label>
                              <div class="mt-1">
                                <input type="text" id="company" v-model="formData.company"
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border" />
                              </div>
                            </div>

                            <!-- Company Role -->
                            <div>
                              <label for="company_role" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Job Title / Role</label>
                              <div class="mt-1">
                                <input type="text" id="company_role" v-model="formData.company_role"
                                  class="block w-full rounded-md border-slate-300 dark:border-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white px-3 py-2 border" />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-shrink-0 justify-end px-4 py-4 bg-slate-50 dark:bg-slate-900/50">
                  <button type="button" @click="closeModal" class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors">
                    Cancel
                  </button>
                  <button type="submit" :disabled="submitting" class="ml-4 inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:opacity-70 disabled:cursor-not-allowed transition-colors">
                    <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ submitting ? 'Provisioning...' : 'Provision User' }}
                  </button>
                </div>
              </form>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

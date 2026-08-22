<script setup>
import { useAuthStore } from '../../Users/stores/useAuthStore'
import { useTheme } from '../composables/useTheme'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const authStore = useAuthStore()
const { isDark, toggleTheme } = useTheme()
const route = useRoute()

const navigation = [
  { name: 'Dashboard', path: '/dashboard', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>' },
  { name: 'Jobs', path: '/jobs', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' },
  { name: 'Applications', path: '/applications', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>' },
  { name: 'Booking', path: '/booking', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>' },
  { name: 'Settings', path: '/settings', icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>' },
]

const isActive = (path) => route.path === path
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-[#0B1120] flex transition-colors duration-300">
    
    <!-- Sidebar -->
      <aside class="w-64 bg-slate-900/95 dark:bg-[#0B1120] backdrop-blur-xl flex-shrink-0 flex flex-col hidden md:flex border-r border-slate-200/10 shadow-2xl relative z-20">
      <div class="h-16 flex items-center px-6 bg-slate-900/50 border-b border-white/5 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20 opacity-50"></div>
        <div class="w-8 h-8 rounded bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-lg relative z-10">A</div>
        <span class="ml-3 text-xl font-bold text-white tracking-wide relative z-10">AVO Portal</span>
      </div>
      
      <div class="p-4 flex-1">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-3">Main Menu</div>
        <nav class="space-y-1.5">
          <router-link 
            v-for="item in navigation" 
            :key="item.name" 
            :to="item.path"
            :class="[
              isActive(item.path) 
                ? 'bg-blue-600/10 text-blue-400 border border-blue-500/20' 
                : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 border border-transparent',
              'flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group'
            ]"
          >
            <span class="mr-3 flex-shrink-0 transition-opacity" :class="{ 'opacity-100 text-blue-500': isActive(item.path), 'opacity-70 group-hover:opacity-100': !isActive(item.path) }" v-html="item.icon"></span>
            {{ item.name }}
          </router-link>
        </nav>
      </div>

      <div class="p-4 border-t border-white/5">
        <button @click="authStore.logout" class="w-full flex items-center px-4 py-2.5 text-sm font-medium text-slate-400 rounded-lg hover:bg-red-500/10 hover:text-red-400 hover:border-red-500/20 border border-transparent transition-all group">
          <span class="mr-3 opacity-70 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          </span> 
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
      <!-- Background Ambient -->
      <div class="absolute top-0 -left-4 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-10 dark:opacity-[0.03] pointer-events-none"></div>
      <div class="absolute top-0 -right-4 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-10 dark:opacity-[0.03] pointer-events-none"></div>

      <!-- Top header -->
      <header class="h-16 bg-white/80 dark:bg-[#0B1120]/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/50 flex items-center justify-between px-8 z-10 sticky top-0">
        <div class="text-slate-900 dark:text-white font-semibold text-lg flex items-center gap-3">
          <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
          Recruiter Workspace
        </div>
        
        <div class="flex items-center gap-5">
          <button @click="toggleTheme" class="p-2 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <span v-if="isDark">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
            <span v-else>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </span>
          </button>
          
          <div class="flex items-center gap-3 pl-5 border-l border-slate-200 dark:border-slate-800">
            <div class="flex flex-col text-right">
              <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ authStore.user?.name || 'Recruiter' }}</span>
              <span class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">RECRUITER</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-slate-200 dark:bg-slate-800 overflow-hidden border border-slate-300 dark:border-slate-700 shadow-sm">
              <img src="https://ui-avatars.com/api/?name=Recruiter&background=random" alt="Avatar" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
      </header>

      <!-- Scrollable content -->
      <main class="flex-1 overflow-auto bg-slate-50 dark:bg-slate-950 p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

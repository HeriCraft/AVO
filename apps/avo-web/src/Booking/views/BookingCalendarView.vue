<script setup>
import { ref } from 'vue'

const currentMonth = ref('August 2026')
const searchQuery = ref('')
const selectedDate = ref(null)

const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const dates = Array.from({ length: 35 }, (_, i) => ({
  day: i - 3,
  isCurrentMonth: i > 3 && i <= 34,
  events: (i === 12 || i === 18) ? [{ id: 1, title: 'AI Interview with John Doe', time: '10:00 AM' }] : []
}))

</script>

<template>
  <div class="h-full flex flex-col">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Booking Calendar</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage candidate interviews scheduled by CALL-E.</p>
      </div>
      <div class="mt-4 sm:mt-0 flex gap-4 items-center">
        <div class="relative">
          <input 
            type="text" 
            v-model="searchQuery"
            placeholder="Search candidate..." 
            class="block w-64 pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg leading-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        <button class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-700 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700">
          Sync Calendar
        </button>
      </div>
    </div>

    <!-- Calendar Card -->
    <div class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden flex flex-col">
      <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center">
          <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ currentMonth }}</h2>
        </div>
        <div class="flex items-center space-x-4">
          <button class="p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          </button>
          <button class="px-3 py-1 text-sm font-medium rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-900 dark:text-white">Today</button>
          <button class="p-2 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </button>
        </div>
      </div>
      
      <div class="flex-1 flex flex-col">
        <!-- Days Header -->
        <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50">
          <div v-for="day in days" :key="day" class="py-2 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            {{ day }}
          </div>
        </div>
        
        <!-- Grid -->
        <div class="flex-1 grid grid-cols-7 grid-rows-5 gap-px bg-slate-200 dark:bg-slate-800">
          <div 
            v-for="(date, i) in dates" 
            :key="i"
            class="min-h-[100px] bg-white dark:bg-slate-900 p-2 transition-colors"
            :class="[
              !date.isCurrentMonth ? 'bg-slate-50/50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-600' : 'text-slate-900 dark:text-slate-200',
              selectedDate === i ? 'ring-2 ring-inset ring-blue-500 z-10' : ''
            ]"
            @click="selectedDate = i"
          >
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium" :class="{'text-white bg-blue-600 rounded-full w-6 h-6 flex items-center justify-center': i === 15}">
                {{ date.day > 0 ? (date.day > 31 ? date.day - 31 : date.day) : 31 + date.day }}
              </span>
            </div>
            
            <div class="mt-2 space-y-1">
              <div 
                v-for="event in date.events" 
                :key="event.id"
                class="px-2 py-1 text-xs rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 truncate cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800/50"
              >
                <span class="font-medium">{{ event.time }}</span> - {{ event.title }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

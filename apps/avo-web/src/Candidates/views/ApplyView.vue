<template>
  <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Step 1: Upload -->
    <div v-if="step === 1" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Apply for this Position</h1>
        <p class="mt-2 text-slate-500 dark:text-slate-400">Upload your resume and our AI will automatically extract your profile.</p>
      </div>

      <div 
        class="border-2 border-dashed rounded-xl p-12 text-center transition-colors"
        :class="isDragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-slate-300 dark:border-slate-700 hover:border-indigo-400 dark:hover:border-indigo-500'"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
      >
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <div class="mt-4 flex text-sm text-slate-600 dark:text-slate-300 justify-center">
          <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none">
            <span>Upload a file</span>
            <input id="file-upload" name="file-upload" type="file" class="sr-only" @change="handleFileSelect" accept=".pdf,.doc,.docx" />
          </label>
          <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">PDF, DOC up to 10MB</p>
        
        <div v-if="selectedFile" class="mt-6 inline-flex items-center px-4 py-2 rounded-full bg-slate-100 dark:bg-slate-800 text-sm font-medium text-slate-800 dark:text-slate-200">
          <svg class="mr-2 h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          {{ selectedFile.name }}
        </div>
      </div>

      <div class="mt-8 flex justify-end">
        <button 
          @click="startUpload" 
          :disabled="!selectedFile"
          class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Transition: Processing -->
    <div v-else-if="step === 1.5" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-12 text-center">
      <svg class="animate-spin mx-auto h-12 w-12 text-indigo-500 mb-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <h3 class="text-xl font-semibold text-slate-900 dark:text-white">{{ progressMessage }}</h3>
      <div class="mt-6 w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden relative">
        <div class="bg-indigo-600 h-2 absolute left-0 top-0 bottom-0 transition-all duration-300 ease-out" :style="{ width: progress + '%' }"></div>
      </div>
    </div>

    <!-- Step 2: Validate Data Form -->
    <div v-else-if="step === 2" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="p-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Verify Your Profile</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">Our AI extracted this information from your resume. Please review and correct any errors.</p>
      </div>

      <div class="p-8 space-y-8">
        <!-- Personal Info -->
        <section>
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">Personal Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">First Name (Required)</label>
              <input type="text" v-model="formData.firstname" required class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Last Name</label>
              <input type="text" v-model="formData.lastname" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email (Required)</label>
              <input type="email" v-model="formData.email" required class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone Number</label>
              <input type="text" v-model="formData.phone_number" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Country</label>
              <input type="text" v-model="formData.country" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Professional Summary</label>
              <textarea v-model="formData.summary" rows="3" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
          </div>
        </section>

        <!-- Experience -->
        <section>
          <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Experience</h3>
            <button @click="addExperience" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">+ Add</button>
          </div>
          
          <div v-for="(exp, index) in formData.experiences" :key="'exp-'+index" class="mb-6 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg relative group">
            <button @click="removeExperience(index)" class="absolute top-2 right-2 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-500">Company</label>
                <input type="text" v-model="exp.company" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label class="block text-xs text-slate-500">Contract Type</label>
                <input type="text" v-model="exp.contract_type" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-xs text-slate-500">From</label>
                  <input type="text" v-model="exp.from" placeholder="MM/YYYY" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                  <label class="block text-xs text-slate-500">To</label>
                  <input type="text" v-model="exp.to" placeholder="Present" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-slate-500">Description</label>
                <textarea v-model="exp.description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>
          </div>
        </section>

        <!-- Education -->
        <section>
          <div class="flex justify-between items-center mb-4 border-b border-slate-200 dark:border-slate-700 pb-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Education</h3>
            <button @click="addEducation" class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">+ Add</button>
          </div>
          
          <div v-for="(edu, index) in formData.educations" :key="'edu-'+index" class="mb-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg relative group">
            <button @click="removeEducation(index)" class="absolute top-2 right-2 text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-slate-500">Institute</label>
                <input type="text" v-model="edu.institute" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
              <div>
                <label class="block text-xs text-slate-500">Degree & Field</label>
                <input type="text" :value="(edu.degree || '') + (edu.degree && edu.field ? ' in ' : '') + (edu.field || '')" @input="updateDegreeField(index, $event.target.value)" class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="px-8 py-6 bg-slate-50 dark:bg-slate-950/50 border-t border-slate-200 dark:border-slate-800 flex justify-end">
        <button 
          @click="submitApplication" 
          :disabled="isSubmitting || !formData.email"
          class="inline-flex justify-center items-center py-2.5 px-8 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Validate & Submit
        </button>
      </div>
    </div>

    <!-- Step 3: Success -->
    <div v-else-if="step === 3" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-12 text-center">
      <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-6">
        <svg class="h-12 w-12 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-4">Application Sent!</h2>
      <p class="text-lg text-slate-600 dark:text-slate-400 mb-8">Thank you for applying. The recruitment team has received your profile and will review it shortly.</p>
      
      <router-link to="/" class="text-indigo-600 dark:text-indigo-400 font-medium hover:underline">
        ← Back to Job Board
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const jobId = route.params.id;

const step = ref(1);
const isDragging = ref(false);
const selectedFile = ref(null);
const trackingId = ref(null);
const isSubmitting = ref(false);

// Transition state
const progress = ref(0);
const progressMessage = ref('Uploading secure file...');
const messages = ['Uploading secure file...', 'Extracting profile data...', 'AI analyzing resume...', 'Finalizing profile...'];
let pollingInterval = null;
let messageInterval = null;

// Form state
const formData = ref({
  firstname: '',
  lastname: '',
  email: '',
  phone_number: '',
  summary: '',
  country: '',
  address: '',
  experiences: [],
  educations: []
});

function handleDrop(e) {
  isDragging.value = false;
  const files = e.dataTransfer.files;
  if (files.length) {
    selectedFile.value = files[0];
  }
}

function handleFileSelect(e) {
  const files = e.target.files;
  if (files.length) {
    selectedFile.value = files[0];
  }
}

async function startUpload() {
  if (!selectedFile.value) return;
  
  step.value = 1.5;
  progress.value = 15;
  
  // Cycle messages
  let msgIdx = 0;
  messageInterval = setInterval(() => {
    msgIdx = (msgIdx + 1) % messages.length;
    progressMessage.value = messages[msgIdx];
    progress.value = Math.min(progress.value + 5, 90);
  }, 2000);

  try {
    const formDataPayload = new FormData();
    formDataPayload.append('resume', selectedFile.value);
    
    // Using absolute URL to avoid proxy issues with Vite in Docker
    const response = await axios.post(`/api/public/apply/${jobId}/upload`, formDataPayload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    trackingId.value = response.data.tracking_id;
    progress.value = 40;
    
    // Start polling
    pollingInterval = setInterval(checkStatus, 3000);
  } catch (error) {
    alert('Upload failed. Please try again.');
    step.value = 1;
    clearInterval(messageInterval);
  }
}

async function checkStatus() {
  if (!trackingId.value) return;
  
  try {
    const response = await axios.get(`/api/public/apply/status/${trackingId.value}`);
    if (response.data.status === 'ready') {
      clearInterval(pollingInterval);
      clearInterval(messageInterval);
      
      const data = response.data.data;
      formData.value.firstname = data.firstname || '';
      formData.value.lastname = data.lastname || '';
      formData.value.email = data.email || '';
      formData.value.phone_number = data.phone_number || '';
      formData.value.summary = data.summary || '';
      formData.value.country = data.country || '';
      formData.value.address = data.address || '';
      formData.value.experiences = data.experiences || [];
      formData.value.educations = data.educations || [];
      
      progress.value = 100;
      setTimeout(() => {
        step.value = 2;
      }, 500);
    }
  } catch (error) {
    console.error('Polling error', error);
  }
}

function addExperience() {
  formData.value.experiences.push({ company: '', contract_type: '', from: '', to: '', description: '' });
}

function removeExperience(index) {
  formData.value.experiences.splice(index, 1);
}

function addEducation() {
  formData.value.educations.push({ institute: '', degree: '', field: '' });
}

function removeEducation(index) {
  formData.value.educations.splice(index, 1);
}

function updateDegreeField(index, value) {
  // Simple hack to bind the combined input back
  formData.value.educations[index].degree = value;
  formData.value.educations[index].field = '';
}

async function submitApplication() {
  isSubmitting.value = true;
  try {
    await axios.post(`/api/public/apply/validate/${trackingId.value}`, formData.value);
    step.value = 3;
  } catch (error) {
    alert('Validation failed. Make sure all required fields are filled.');
  } finally {
    isSubmitting.value = false;
  }
}

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
  if (messageInterval) clearInterval(messageInterval);
});
</script>

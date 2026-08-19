import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import eventBus from './Shared/events/eventBus';
import './style.css';

import { useAuthStore } from './Users/stores/useAuthStore';

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);

// Initialize session from localStorage before routing
const authStore = useAuthStore(pinia);
authStore.initSession();

app.use(router);

// Provide event bus globally
app.provide('eventBus', eventBus);

app.mount('#app');

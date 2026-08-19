import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import eventBus from './Shared/events/eventBus';
import './style.css';

const app = createApp(App);

app.use(createPinia());
app.use(router);

// Provide event bus globally
app.provide('eventBus', eventBus);

app.mount('#app');

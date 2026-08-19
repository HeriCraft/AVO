<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from './Users';
import { useRouter } from 'vue-router';
import { onMounted, onUnmounted } from 'vue';
import eventBus from './Shared/events/eventBus';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const onAuthLogout = () => {
  if (router.currentRoute.value.path !== '/login') {
    router.push('/login');
  }
};

onMounted(() => {
  eventBus.on('auth:logout', onAuthLogout);
});

onUnmounted(() => {
  eventBus.off('auth:logout', onAuthLogout);
});
</script>

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0', // Expose sur le réseau Docker
    port: 5173,
    hmr: {
      host: 'app.avo.local', // Les connexions WebSocket se font via ce domaine
      clientPort: 80,        // à travers le reverse proxy Nginx
    },
    watch: {
      usePolling: true, // Nécessaire sous Docker/WSL
    }
  }
})

import { ref, onMounted } from 'vue'

export function useTheme() {
  const isDark = ref(false)

  const applyTheme = (dark) => {
    isDark.value = dark
    if (dark) {
      document.documentElement.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    }
  }

  const toggleTheme = () => {
    applyTheme(!isDark.value)
  }

  onMounted(() => {
    const stored = localStorage.getItem('theme')
    if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      applyTheme(true)
    } else {
      applyTheme(false)
    }
  })

  return {
    isDark,
    toggleTheme,
    applyTheme
  }
}

import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useAuthStore } from '../../src/Users/stores/useAuthStore'
import apiClient from '../../src/Shared/http/client'

vi.mock('../../src/Shared/http/client', () => {
  return {
    default: {
      post: vi.fn(),
      defaults: {
        headers: {
          common: {}
        }
      }
    }
  }
})

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    delete apiClient.defaults.headers.common['Authorization']
  })

  it('sets session and computes isSuperAdmin correctly for SUPER_ADMIN', () => {
    const store = useAuthStore()
    
    store.setSession('test-token', { name: 'GRANIX', role: 'SUPER_ADMIN' })
    
    expect(store.token).toBe('test-token')
    expect(store.isAuthenticated).toBe(true)
    expect(store.isSuperAdmin).toBe(true)
    expect(localStorage.getItem('access_token')).toBe('test-token')
    expect(apiClient.defaults.headers.common['Authorization']).toBe('Bearer test-token')
  })

  it('sets session and computes isSuperAdmin correctly for regular USER', () => {
    const store = useAuthStore()
    
    store.setSession('test-token', { name: 'John Doe', role: 'USER' })
    
    expect(store.token).toBe('test-token')
    expect(store.isAuthenticated).toBe(true)
    expect(store.isSuperAdmin).toBe(false)
  })

  it('clears session correctly on logout', () => {
    const store = useAuthStore()
    
    store.setSession('test-token', { name: 'GRANIX', role: 'SUPER_ADMIN' })
    store.clearSession()
    
    expect(store.token).toBe(null)
    expect(store.isAuthenticated).toBe(false)
    expect(store.isSuperAdmin).toBe(false)
    expect(localStorage.getItem('access_token')).toBe(null)
    expect(apiClient.defaults.headers.common['Authorization']).toBeUndefined()
  })
})

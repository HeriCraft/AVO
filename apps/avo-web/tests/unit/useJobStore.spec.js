import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useJobStore } from '../../src/Jobs/stores/useJobStore'
import apiClient from '../../src/Shared/http/client'

vi.mock('../../src/Shared/http/client', () => {
  return {
    default: {
      get: vi.fn(),
      post: vi.fn(),
    }
  }
})

describe('useJobStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('fetches jobs successfully', async () => {
    const store = useJobStore()
    const mockJobs = [{ id: 1, title: 'AI Dev', status: 'PUBLISHED' }]
    apiClient.get.mockResolvedValueOnce({ data: mockJobs })

    await store.fetchJobs()

    expect(apiClient.get).toHaveBeenCalledWith('/jobs')
    expect(store.jobs).toEqual(mockJobs)
    expect(store.loading).toBe(false)
  })

  it('handles fetch jobs failure', async () => {
    const store = useJobStore()
    apiClient.get.mockRejectedValueOnce(new Error('Network error'))

    await store.fetchJobs()

    expect(store.error).toBe('Failed to load jobs')
    expect(store.loading).toBe(false)
  })

  it('creates a job successfully', async () => {
    const store = useJobStore()
    const newJob = { id: 2, title: 'Designer', status: 'DRAFT' }
    apiClient.post.mockResolvedValueOnce({ data: newJob })

    const result = await store.createJob({ title: 'Designer' })

    expect(apiClient.post).toHaveBeenCalledWith('/jobs', { title: 'Designer' })
    expect(result.success).toBe(true)
    expect(store.jobs[0]).toEqual(newJob)
    expect(store.loading).toBe(false)
  })
})

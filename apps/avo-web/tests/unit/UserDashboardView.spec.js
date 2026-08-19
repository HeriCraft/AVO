import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import UserDashboardView from '../../src/Dashboard/views/UserDashboardView.vue'
import apiClient from '../../src/Shared/http/client'

// Mock apiClient
vi.mock('../../src/Shared/http/client', () => {
  return {
    default: {
      get: vi.fn()
    }
  }
})

// Mock chart.js so it doesn't crash in JSDOM
vi.mock('chart.js', () => ({
  Chart: { register: vi.fn() },
  Title: {}, Tooltip: {}, Legend: {}, BarElement: {}, CategoryScale: {}, LinearScale: {}, ArcElement: {}, PointElement: {}, LineElement: {}
}))
vi.mock('vue-chartjs', () => ({
  Bar: { template: '<div>Bar Chart</div>' },
  Doughnut: { template: '<div>Doughnut Chart</div>' },
  Line: { template: '<div>Line Chart</div>' }
}))

describe('UserDashboardView.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    
    // Default mock response for metrics
    apiClient.get.mockImplementation((url) => {
      if (url === '/user/dashboard/metrics') {
        return Promise.resolve({
          data: {
            kpis: { pending_action_count: 5, ai_interviews_30d: 10, active_jobs_count: 3, conversion_rate: 15 },
            charts: { ai_scores: {}, funnel: {}, acquisition: {} },
            widgets: { top_green_candidates: [], todays_interviews: [] }
          }
        })
      }
      if (url.includes('/user/dashboard/details')) {
        return Promise.resolve({
          data: {
            type: url.includes('pending_actions') ? 'candidates' : 'jobs',
            data: [{ id: 1, name: 'Mock Data' }]
          }
        })
      }
      return Promise.reject(new Error('not found'))
    })
  })

  it('renders KPI cards and default Action Center table', async () => {
    const wrapper = mount(UserDashboardView)
    
    // Wait for onMounted to fetch initial data
    await flushPromises()
    
    // Ensure 4 KPI buttons are rendered
    const kpiButtons = wrapper.findAll('button.group')
    expect(kpiButtons.length).toBe(3) // Wait, conversion rate isn't a button in the current template
    
    // Active filter should be pending_actions by default
    expect(apiClient.get).toHaveBeenCalledWith('/user/dashboard/details?filter=pending_actions')
    
    // The Action Center label should show Pending Review
    expect(wrapper.html()).toContain('Pending Review')
    expect(wrapper.html()).toContain('Candidate Name') // The candidates table header
  })

  it('changes active filter and refetches data when KPI card is clicked', async () => {
    const wrapper = mount(UserDashboardView)
    await flushPromises()
    
    apiClient.get.mockClear() // clear initial mount calls
    
    // Click the second KPI button (Active Jobs)
    // Wait, the order in template is: 1. Pending Actions, 2. AI Interviews, 3. Active Jobs
    const kpiButtons = wrapper.findAll('button.group')
    await kpiButtons[2].trigger('click')
    
    expect(apiClient.get).toHaveBeenCalledWith('/user/dashboard/details?filter=active_jobs')
    
    await flushPromises()
    
    // Action center label should change
    expect(wrapper.html()).toContain('Active Jobs')
    // Table should change to jobs layout
    expect(wrapper.html()).toContain('Job Title')
    expect(wrapper.html()).not.toContain('Candidate Name')
  })
})

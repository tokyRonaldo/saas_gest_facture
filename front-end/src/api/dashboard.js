import api from '@/api/axios'

export const dashboardApi = {
  admin() {
    return api.get('/api/dashboard/admin')
  },
  simple() {
    return api.get('/api/dashboard/simple')
  },
}
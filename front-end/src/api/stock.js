import api from '@/api/axios'

export const stockApi = {
  list(params = {}) {
    return api.get('/api/stock-movements', { params })
  },
  create(payload) {
    return api.post('/api/stock-movements', payload)
  },
}
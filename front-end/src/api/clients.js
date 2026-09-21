import api from '@/api/axios'

export const clientsApi = {
  list(params = {}) {
    return api.get('/api/clients', { params })
  },
  get(id) {
    return api.get(`/api/clients/${id}`)
  },
  create(payload) {
    return api.post('/api/clients', payload)
  },
  update(id, payload) {
    return api.put(`/api/clients/${id}`, payload)
  },
  remove(id) {
    return api.delete(`/api/clients/${id}`)
  },
}
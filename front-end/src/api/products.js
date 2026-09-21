import api from '@/api/axios'

export const productsApi = {
  list(params = {}) {
    return api.get('/api/products', { params })
  },
  get(id) {
    return api.get(`/api/products/${id}`)
  },
  create(payload) {
    return api.post('/api/products', payload)
  },
  update(id, payload) {
    return api.put(`/api/products/${id}`, payload)
  },
  remove(id) {
    return api.delete(`/api/products/${id}`)
  },
}
// src/api/users.js
import api from '@/api/axios'

export const usersApi = {
  list(params = {}) {
    return api.get('/api/users', { params })
  },
  get(id) {
    return api.get(`/api/users/${id}`)
  },
  create(payload) {
    return api.post('/api/users', payload)
  },
  update(id, payload) {
    return api.put(`/api/users/${id}`, payload)
  },
  toggleActif(id) {
    return api.patch(`/api/users/${id}/toggle-actif`)
  },
  remove(id) {
    return api.delete(`/api/users/${id}`)
  },
}
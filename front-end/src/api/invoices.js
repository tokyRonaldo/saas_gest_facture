import api from '@/api/axios'

export const invoicesApi = {
  list(params = {}) {
    return api.get('/api/invoices', { params })
  },
  get(id) {
    return api.get(`/api/invoices/${id}`)
  },
  create(payload) {
    return api.post('/api/invoices', payload)
  },
  update(id, payload) {
    return api.put(`/api/invoices/${id}`, payload)
  },
  envoyer(id) {
    return api.post(`/api/invoices/${id}/envoyer`)
  },
  annuler(id) {
    return api.post(`/api/invoices/${id}/annuler`)
  },
  remove(id) {
    return api.delete(`/api/invoices/${id}`)
  },
}
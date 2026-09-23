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

  async downloadPdf(id, numero) {
    const response = await api.get(`/api/invoices/${id}/pdf`, {
      responseType: 'blob',
    })

    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `${numero || 'facture'}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  },
}
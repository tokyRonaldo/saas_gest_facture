import api from '@/api/axios'

export const paymentsApi = {
  list(invoiceId) {
    return api.get(`/api/invoices/${invoiceId}/payments`)
  },
  create(invoiceId, payload) {
    return api.post(`/api/invoices/${invoiceId}/payments`, payload)
  },
  remove(paymentId) {
    return api.delete(`/api/payments/${paymentId}`)
  },
}
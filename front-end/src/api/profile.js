import api from '@/api/axios'

export const profileApi = {
  update(payload) {
    return api.put('/api/me', payload)
  },
}
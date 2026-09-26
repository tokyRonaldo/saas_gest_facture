import api from '@/api/axios'

const API_URL = import.meta.env.VITE_API_URL

export const settingsApi = {
  get() {
    return api.get('/api/settings')
  },
  update(payload) {
    return api.put('/api/settings', payload)
  },
  uploadLogo(file) {
    const formData = new FormData()
    formData.append('logo', file)
    return api.post('/api/settings/logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
  logoUrl(logoPath) {
    if (!logoPath) return null
    return `${API_URL}/storage/${logoPath}`
  },
}
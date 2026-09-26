// src/stores/settings.js
import { defineStore } from 'pinia'
import { settingsApi } from '@/api/settings'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    settings: null,
    loaded: false,
  }),
  actions: {
    async fetch() {
      if (this.loaded) return
      const { data } = await settingsApi.get()
      this.settings = data
      this.loaded = true
    },
    // Appelé après un update dans Settings.vue pour rafraîchir la sidebar immédiatement
    updateLocal(data) {
      this.settings = data
    },
  },
})
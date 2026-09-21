<!-- src/views/Login.vue -->
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const auth = useAuthStore()
const router = useRouter()

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch {
    error.value = 'Email ou mot de passe incorrect'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex bg-white">
    <!-- Panneau de marque -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-between p-12 text-white">
      <div class="flex items-center gap-2">
        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center font-bold">
          F
        </div>
        <span class="text-xl font-bold">FacturaApp</span>
      </div>

      <div>
        <h1 class="text-3xl font-bold leading-snug mb-4">
          Gérez votre facturation en toute simplicité
        </h1>
        <p class="text-slate-400 text-sm leading-relaxed">
          Clients, produits, factures et paiements centralisés dans un seul tableau de bord.
        </p>
      </div>

      <p class="text-slate-500 text-xs">© 2026 FacturaApp — v1.0.0</p>
    </div>

    <!-- Formulaire -->
    <div class="flex-1 flex items-center justify-center p-8">
      <div class="w-full max-w-sm">
        <div class="lg:hidden flex items-center gap-2 mb-8">
          <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">
            F
          </div>
          <span class="text-xl font-bold text-slate-900">FacturaApp</span>
        </div>

        <h2 class="text-2xl font-bold text-slate-900 mb-1">Connexion</h2>
        <p class="text-slate-500 text-sm mb-8">Accédez à votre espace de gestion</p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="vous@entreprise.com"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent
                     transition"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Mot de passe</label>
            <input
              v-model="password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent
                     transition"
            />
          </div>

          <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg">
            {{ error }}
          </p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 text-white font-medium text-sm py-2.5 rounded-lg
                   hover:bg-blue-700 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
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

const demoAccounts = [
  {
    role: 'Utilisateur',
    email: 'testuser@gmail.com',
    password: 'password',
  },
  {
    role: 'Commercial',
    email: 'testcommercial@gmail.com',
    password: 'password',
  },
]

function useDemoAccount(account) {
  email.value = account.email
  password.value = account.password
  error.value = ''
}

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
    <div
      class="hidden lg:flex lg:w-1/2 bg-slate-900 flex-col justify-between p-12 text-white"
    >
      <div class="flex items-center gap-2">
        <div
          class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center font-bold"
        >
          F
        </div>

        <span class="text-xl font-bold">
          FacturaApp
        </span>
      </div>

      <div>
        <h1 class="text-3xl font-bold leading-snug mb-4">
          Gérez votre facturation en toute simplicité
        </h1>

        <p class="text-slate-400 text-sm leading-relaxed">
          Clients, produits, factures et paiements centralisés
          dans un seul tableau de bord.
        </p>
      </div>

      <p class="text-slate-500 text-xs">
        © 2026 FacturaApp — v1.0.0
      </p>
    </div>

    <!-- Formulaire -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-8">
      <div class="w-full max-w-sm">

        <!-- Logo mobile -->
        <div class="lg:hidden flex items-center gap-2 mb-8">
          <div
            class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold"
          >
            F
          </div>

          <span class="text-xl font-bold text-slate-900">
            FacturaApp
          </span>
        </div>

        <h2 class="text-2xl font-bold text-slate-900 mb-1">
          Connexion
        </h2>

        <p class="text-slate-500 text-sm mb-6">
          Accédez à votre espace de gestion
        </p>


        <!-- Comptes de démonstration -->
        <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50/50 p-4">

          <div class="flex items-center justify-between mb-3">
            <div>
              <h3 class="text-sm font-semibold text-slate-800">
                Comptes de démonstration
              </h3>

              <p class="text-xs text-slate-500 mt-0.5">
                Cliquez sur un compte pour remplir le formulaire.
              </p>
            </div>
          </div>

          <div class="space-y-2">

            <!-- Compte utilisateur -->
            <button
              v-for="account in demoAccounts"
              :key="account.email"
              type="button"
              @click="useDemoAccount(account)"
              class="w-full text-left p-3 rounded-lg bg-white border border-slate-200
                     hover:border-blue-400 hover:bg-blue-50 transition"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-slate-800">
                  {{ account.role }}
                </span>

                <span
                  class="text-[11px] font-medium text-blue-600 bg-blue-50
                         px-2 py-0.5 rounded-full"
                >
                  Démo
                </span>
              </div>

              <p class="text-xs text-slate-500">
                {{ account.email }}
              </p>

              <p class="text-xs text-slate-400 mt-0.5">
                Mot de passe : {{ account.password }}
              </p>
            </button>

          </div>
        </div>


        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-4">

          <div>
            <label
              class="block text-sm font-medium text-slate-700 mb-1.5"
            >
              Email
            </label>

            <input
              v-model="email"
              type="email"
              required
              placeholder="vous@entreprise.com"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300
                     text-sm focus:outline-none focus:ring-2 focus:ring-blue-600
                     focus:border-transparent transition"
            />
          </div>

          <div>
            <label
              class="block text-sm font-medium text-slate-700 mb-1.5"
            >
              Mot de passe
            </label>

            <input
              v-model="password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300
                     text-sm focus:outline-none focus:ring-2 focus:ring-blue-600
                     focus:border-transparent transition"
            />
          </div>

          <p
            v-if="error"
            class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg"
          >
            {{ error }}
          </p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 text-white font-medium text-sm py-2.5
                   rounded-lg hover:bg-blue-700 transition
                   disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Connexion...' : 'Se connecter' }}
          </button>

        </form>

      </div>
    </div>
  </div>
</template>
<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const navItems = [
  { label: 'Dashboard', to: '/', icon: '📊' },
  { label: 'Factures', to: '/factures', icon: '📄' },
  { label: 'Produits', to: '/produits', icon: '📦' },
  { label: 'Clients', to: '/clients', icon: '👥' },
  { label: 'Utilisateurs', to: '/utilisateurs', icon: '👤' },
]

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col">
      <div class="p-5 flex items-center gap-2 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-sm">
          F
        </div>
        <span class="font-bold text-lg">FacturaApp</span>
      </div>

      <nav class="flex-1 p-3 space-y-1">
        <router-link
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition"
          :class="route.path === item.to
            ? 'bg-blue-600 text-white'
            : 'text-slate-300 hover:bg-slate-800'"
        >
          <span>{{ item.icon }}</span>
          {{ item.label }}
        </router-link>
      </nav>
    </aside>

    <!-- Contenu -->
    <div class="flex-1 flex flex-col">
      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
        <div class="text-sm text-slate-500">
          {{ route.meta.breadcrumb || '' }}
        </div>

        <div class="flex items-center gap-4">
          <div class="text-right">
            <p class="text-sm font-medium text-slate-900">{{ auth.user?.name }}</p>
            <p class="text-xs text-slate-500">{{ auth.user?.roles?.[0] }}</p>
          </div>
          <button
            @click="logout"
            class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-semibold text-sm
                   flex items-center justify-center hover:bg-blue-200 transition"
            title="Déconnexion"
          >
            {{ auth.user?.name?.[0] }}
          </button>
        </div>
      </header>

      <!-- Page -->
      <main class="flex-1 p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>
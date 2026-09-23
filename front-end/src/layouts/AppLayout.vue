<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { ChevronRightIcon, UserCircleIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

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

const roleLabel = { admin: 'Administrateur', user: 'Utilisateur', commercial: 'Commercial' }

// --- Dropdown utilisateur ---
const showUserMenu = ref(false)
const menuRef = ref(null)

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value
}

function closeOnClickOutside(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => document.addEventListener('click', closeOnClickOutside))
onUnmounted(() => document.removeEventListener('click', closeOnClickOutside))

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

    <div class="flex-1 flex flex-col min-w-0">
      <header class="h-14 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center px-6 justify-between relative">
        <nav class="flex items-center gap-1.5 text-sm">
          <template v-for="(crumb, i) in route.meta.breadcrumb || []" :key="i">
            <ChevronRightIcon v-if="i > 0" class="w-3.5 h-3.5 text-slate-300" />
            <router-link
              v-if="crumb.to"
              :to="crumb.to"
              class="text-slate-400 hover:text-slate-700 transition-colors font-medium"
            >
              {{ crumb.label }}
            </router-link>
            <span v-else class="text-slate-900 font-medium">{{ crumb.label }}</span>
          </template>
        </nav>

        <!-- Zone utilisateur avec dropdown -->
        <div ref="menuRef" class="relative">
          <button
            @click="toggleUserMenu"
            class="flex items-center gap-3 hover:bg-slate-100 rounded-lg px-2 py-1.5 transition"
          >
            <div class="text-right">
              <p class="text-sm font-medium text-slate-900">{{ auth.user?.name }}</p>
              <p class="text-xs text-slate-500">{{ roleLabel[auth.user?.roles?.[0]] }}</p>
            </div>
            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-semibold text-sm
                        flex items-center justify-center shrink-0">
              {{ auth.user?.name?.[0] }}
            </div>
          </button>

          <!-- Dropdown -->
          <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="showUserMenu"
              class="absolute right-0 top-full mt-2 w-64 bg-white rounded-xl border border-slate-200 shadow-lg shadow-slate-900/10 overflow-hidden z-50"
            >
              <div class="px-4 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-900">{{ auth.user?.name }}</p>
                <p class="text-xs text-slate-500">{{ auth.user?.email }}</p>
              </div>

              <div class="py-1.5">
                <router-link
                  to="/profil"
                  @click="showUserMenu = false"
                  class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition"
                >
                  <UserCircleIcon class="w-4 h-4 text-slate-400" />
                  Mon profil
                </router-link>
                <router-link
                  v-if="auth.user?.roles?.includes('admin')"
                  to="/parametres"
                  @click="showUserMenu = false"
                  class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition"
                >
                  <Cog6ToothIcon class="w-4 h-4 text-slate-400" />
                  Paramètres
                </router-link>
              </div>

              <div class="border-t border-slate-100 py-1.5">
                <button
                  @click="logout"
                  class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                >
                  <ArrowRightOnRectangleIcon class="w-4 h-4" />
                  Déconnexion
                </button>
              </div>
            </div>
          </transition>
        </div>
      </header>

      <main class="flex-1 p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>
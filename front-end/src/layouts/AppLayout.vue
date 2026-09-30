<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useSettingsStore } from '@/stores/settings'
import { settingsApi } from '@/api/settings'
import { useRouter, useRoute } from 'vue-router'
import {
  ChevronRightIcon, UserCircleIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon,
  Bars3Icon, XMarkIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const settingsStore = useSettingsStore()
const router = useRouter()
const route = useRoute()

const allNavItems = [
  { label: 'Dashboard', to: '/', icon: '📊', permission: 'dashboard.view' },
  { label: 'Factures', to: '/factures', icon: '📄', permission: 'invoices.view' },
  { label: 'Produits', to: '/produits', icon: '📦', permission: 'products.view' },
  { label: 'Clients', to: '/clients', icon: '👥', permission: 'clients.view' },
  { label: 'Utilisateurs', to: '/utilisateurs', icon: '👤', permission: 'users.view' },
]

const navItems = computed(() =>
  allNavItems.filter(item => !item.permission || auth.can(item.permission))
)

const roleLabel = { admin: 'Administrateur', user: 'Utilisateur', commercial: 'Commercial' }

// --- Sidebar mobile ---
const showSidebar = ref(false)
function closeSidebar() {
  showSidebar.value = false
}
// Ferme automatiquement la sidebar mobile après un changement de page
watch(() => route.path, closeSidebar)

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

onMounted(() => {
  settingsStore.fetch()
  document.addEventListener('click', closeOnClickOutside)
})
onUnmounted(() => document.removeEventListener('click', closeOnClickOutside))

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50">
    <!-- Overlay mobile, ferme la sidebar au clic -->
    <div
      v-if="showSidebar"
      @click="closeSidebar"
      class="fixed inset-0 bg-black/40 z-30 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-200 lg:static lg:translate-x-0"
      :class="showSidebar ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="p-5 flex items-center justify-between gap-2 border-b border-slate-800">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-8 h-8 rounded-lg overflow-hidden bg-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
            <img
              v-if="settingsStore.settings?.logo_path"
              :src="settingsApi.logoUrl(settingsStore.settings.logo_path)"
              alt="Logo"
              class="w-full h-full object-contain"
            />
            <span v-else>{{ settingsStore.settings?.nom_entreprise?.[0] || 'F' }}</span>
          </div>
          <span class="font-bold text-lg truncate">
            {{ settingsStore.settings?.nom_entreprise || 'FacturaApp' }}
          </span>
        </div>
        <button @click="closeSidebar" class="lg:hidden text-slate-400 hover:text-white shrink-0">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
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
      <header class="h-14 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center px-4 lg:px-6 justify-between relative gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <button @click="showSidebar = true" class="lg:hidden text-slate-500 hover:text-slate-900 shrink-0">
            <Bars3Icon class="w-6 h-6" />
          </button>

          <nav class="flex items-center gap-1.5 text-sm overflow-x-auto whitespace-nowrap">
            <template v-for="(crumb, i) in route.meta.breadcrumb || []" :key="i">
              <ChevronRightIcon v-if="i > 0" class="w-3.5 h-3.5 text-slate-300 shrink-0" />
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
        </div>

        <!-- Zone utilisateur avec dropdown -->
        <div ref="menuRef" class="relative shrink-0">
          <button
            @click="toggleUserMenu"
            class="flex items-center gap-3 hover:bg-slate-100 rounded-lg px-2 py-1.5 transition"
          >
            <div class="text-right hidden sm:block">
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
                  v-if="auth.can('settings.manage')"
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

      <main class="flex-1 p-4 lg:p-6 overflow-x-hidden">
        <router-view />
      </main>
    </div>
  </div>
</template>
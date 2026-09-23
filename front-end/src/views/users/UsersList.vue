<script setup>
import { ref, onMounted, watch } from 'vue'
import { usersApi } from '@/api/users'
import { useAuthStore } from '@/stores/auth'
import ConfirmModal from '@/components/ConfirmModal.vue'
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const users = ref([])
const search = ref('')
const loading = ref(false)
const confirmState = ref(null)

const roleStyle = {
  admin: 'bg-red-100 text-red-700',
  user: 'bg-blue-100 text-blue-700',
  commercial: 'bg-purple-100 text-purple-700',
}
const roleLabel = { admin: 'Admin', user: 'Utilisateur', commercial: 'Commercial' }

async function fetchUsers() {
  loading.value = true
  const { data } = await usersApi.list({ search: search.value })
  users.value = data.data
  loading.value = false
}

let debounce
watch(search, () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchUsers, 300)
})

async function toggleActif(user) {
  await usersApi.toggleActif(user.id)
  fetchUsers()
}

function demanderSuppression(user) {
  confirmState.value = {
    title: 'Supprimer l\'utilisateur',
    message: `Supprimer définitivement le compte de ${user.name} ? Cette action est irréversible.`,
    confirmLabel: 'Supprimer',
    danger: true,
    action: async () => {
      await usersApi.remove(user.id)
      confirmState.value = null
      fetchUsers()
    },
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Utilisateurs</h1>
        <p class="text-sm text-slate-500">Gère les comptes et les rôles de l'équipe</p>
      </div>
      <router-link
        to="/utilisateurs/nouveau"
        class="bg-blue-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-blue-700 transition"
      >
        + Nouvel utilisateur
      </router-link>
    </div>

    <input
      v-model="search"
      type="text"
      placeholder="Rechercher par nom ou email..."
      class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm mb-4
             focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
    />

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
          <tr>
            <th class="text-left px-4 py-3">Nom</th>
            <th class="text-left px-4 py-3">Email</th>
            <th class="text-left px-4 py-3">Rôle</th>
            <th class="text-left px-4 py-3">Statut</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-6 text-slate-400">Chargement...</td></tr>
          <tr v-else-if="users.length === 0"><td colspan="5" class="text-center py-6 text-slate-400">Aucun utilisateur</td></tr>
          <tr v-for="u in users" :key="u.id" class="border-t border-slate-100">
            <td class="px-4 py-3 font-medium text-slate-900">{{ u.name }}</td>
            <td class="px-4 py-3 text-slate-600">{{ u.email }}</td>
            <td class="px-4 py-3">
              <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="roleStyle[u.roles[0]?.name]">
                {{ roleLabel[u.roles[0]?.name] }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button
                @click="toggleActif(u)"
                :disabled="u.id === auth.user?.id"
                class="text-xs font-medium px-2.5 py-1 rounded-full transition disabled:opacity-50 disabled:cursor-not-allowed"
                :class="u.actif ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
              >
                {{ u.actif ? 'Actif' : 'Désactivé' }}
              </button>
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <router-link :to="`/utilisateurs/${u.id}/modifier`" class="text-slate-600 hover:text-slate-900 inline-block align-middle" title="Modifier">
                <PencilIcon class="w-4 h-4" />
              </router-link>
              <button
                v-if="u.id !== auth.user?.id"
                @click="demanderSuppression(u)"
                class="text-red-500 hover:text-red-700 inline-block align-middle"
                title="Supprimer"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmModal
      v-if="confirmState"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-label="confirmState.confirmLabel"
      :danger="confirmState.danger"
      @confirm="confirmState.action"
      @cancel="confirmState = null"
    />
  </div>
</template>
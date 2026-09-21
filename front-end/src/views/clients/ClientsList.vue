```vue
<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'

const clients = ref([])
const search = ref('')
const loading = ref(false)
const deleting = ref(false)

const router = useRouter()

// Client sélectionné pour la suppression
const clientToDelete = ref(null)

// Ouvre la modal
function openDeleteModal(client) {
  clientToDelete.value = client
}

// Ferme la modal
function closeDeleteModal() {
  if (deleting.value) return

  clientToDelete.value = null
}

// Suppression
async function confirmDelete() {
  if (!clientToDelete.value) return

  deleting.value = true

  try {
    await clientsApi.remove(clientToDelete.value.id)

    // Fermer la modal
    clientToDelete.value = null

    // Recharger la liste
    await fetchClients()
  } catch (error) {
    console.error('Erreur lors de la suppression du client :', error)
  } finally {
    deleting.value = false
  }
}

async function fetchClients() {
  loading.value = true

  try {
    const { data } = await clientsApi.list({
      search: search.value
    })

    clients.value = data.data
  } catch (error) {
    console.error('Erreur lors du chargement des clients :', error)
  } finally {
    loading.value = false
  }
}

let debounce

watch(search, () => {
  clearTimeout(debounce)

  debounce = setTimeout(fetchClients, 300)
})

onMounted(fetchClients)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          Clients
        </h1>

        <p class="text-sm text-slate-500">
          Gère la liste de tes clients
        </p>
      </div>

      <button
        @click="router.push('/clients/nouveau')"
        class="bg-blue-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-blue-700 transition"
      >
        + Nouveau client
      </button>
    </div>

    <!-- Recherche -->
    <input
      v-model="search"
      type="text"
      placeholder="Rechercher un client..."
      class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm mb-4
             focus:outline-none focus:ring-2 focus:ring-blue-600"
    />

    <!-- Tableau -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
          <tr>
            <th class="text-left px-4 py-3">Nom</th>
            <th class="text-left px-4 py-3">Email</th>
            <th class="text-left px-4 py-3">Téléphone</th>
            <th class="text-left px-4 py-3">Ville</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>

        <tbody>
          <!-- Loading -->
          <tr v-if="loading">
            <td
              colspan="5"
              class="text-center py-6 text-slate-400"
            >
              Chargement...
            </td>
          </tr>

          <!-- Aucun client -->
          <tr v-else-if="clients.length === 0">
            <td
              colspan="5"
              class="text-center py-6 text-slate-400"
            >
              Aucun client
            </td>
          </tr>

          <!-- Clients -->
          <tr
            v-for="client in clients"
            :key="client.id"
            class="border-t border-slate-100 hover:bg-slate-50/50 transition"
          >
            <td class="px-4 py-3 font-medium text-slate-900">
              {{ client.nom }}
            </td>

            <td class="px-4 py-3 text-slate-600">
              {{ client.email || '—' }}
            </td>

            <td class="px-4 py-3 text-slate-600">
              {{ client.telephone || '—' }}
            </td>

            <td class="px-4 py-3 text-slate-600">
              {{ client.ville || '—' }}
            </td>

            <td class="px-4 py-3 text-right space-x-2">
              <!-- Modifier -->
              <button
                @click="router.push(`/clients/${client.id}/modifier`)"
                class="text-blue-600 hover:text-blue-800 transition"
                title="Modifier"
              >
                ✏️
              </button>

              <!-- Supprimer -->
              <button
                @click="openDeleteModal(client)"
                class="text-red-500 hover:text-red-700 transition"
                title="Supprimer"
              >
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL CONFIRMATION SUPPRESSION -->
    <!-- ===================================================== -->

    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="clientToDelete"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
          <!-- Overlay -->
          <div
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
            @click="closeDeleteModal"
          ></div>

          <!-- Modal -->
          <div
            class="relative w-full max-w-md bg-white rounded-2xl shadow-xl
                   border border-slate-200 overflow-hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
          >
            <!-- Contenu -->
            <div class="p-6">
              <!-- Icône -->
              <div
                class="mx-auto flex items-center justify-center
                       w-12 h-12 rounded-full bg-red-100 mb-4"
              >
                <svg
                  class="w-6 h-6 text-red-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 0 0-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"
                  />
                </svg>
              </div>

              <!-- Titre -->
              <h2
                id="delete-modal-title"
                class="text-lg font-semibold text-slate-900 text-center"
              >
                Supprimer ce client ?
              </h2>

              <!-- Message -->
              <p class="mt-2 text-sm text-slate-500 text-center">
                Es-tu sûr de vouloir supprimer
                <span class="font-semibold text-slate-700">
                  {{ clientToDelete.nom }}
                </span>
                ?
              </p>

              <p class="mt-1 text-xs text-slate-400 text-center">
                Cette action est irréversible.
              </p>
            </div>

            <!-- Footer -->
            <div
              class="flex items-center justify-end gap-3
                     px-6 py-4 bg-slate-50 border-t border-slate-100"
            >
              <!-- Annuler -->
              <button
                type="button"
                @click="closeDeleteModal"
                :disabled="deleting"
                class="px-4 py-2.5 text-sm font-medium text-slate-700
                       bg-white border border-slate-300 rounded-lg
                       hover:bg-slate-50 transition
                       disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Annuler
              </button>

              <!-- Confirmer -->
              <button
                type="button"
                @click="confirmDelete"
                :disabled="deleting"
                class="inline-flex items-center justify-center gap-2
                       px-4 py-2.5 text-sm font-medium text-white
                       bg-red-600 rounded-lg
                       hover:bg-red-700 transition
                       disabled:opacity-60 disabled:cursor-not-allowed
                       min-w-[100px]"
              >
                <!-- Spinner -->
                <svg
                  v-if="deleting"
                  class="w-4 h-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>

                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                  ></path>
                </svg>

                {{ deleting ? 'Suppression...' : 'Supprimer' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>

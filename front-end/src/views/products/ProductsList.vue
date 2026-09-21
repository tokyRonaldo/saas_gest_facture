<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { productsApi } from '@/api/products'
import StockMovementModal from '@/components/StockMovementModal.vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const products = ref([])
const search = ref('')
const loading = ref(false)
const router = useRouter()

const showStockModal = ref(false)
const selectedProduct = ref(null)


async function fetchProducts() {
  loading.value = true
  const { data } = await productsApi.list({ search: search.value })
  products.value = data.data
  loading.value = false
}

let debounce
watch(search, () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchProducts, 300)
})

async function remove(product) {
  if (!confirm(`Supprimer ${product.nom} ?`)) return
  await productsApi.remove(product.id)
  fetchProducts()
}

function formatPrix(prix) {
  return new Intl.NumberFormat('fr-FR').format(prix)
}

function openStockModal(product) {
  selectedProduct.value = product
  showStockModal.value = true
}

onMounted(fetchProducts)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Produits</h1>
        <p class="text-sm text-slate-500">Gère ton catalogue de produits et services</p>
      </div>
      <button
        @click="router.push('/produits/nouveau')"
        class="bg-blue-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-blue-700 transition"
      >
        + Nouveau produit
      </button>
    </div>

    <input
      v-model="search"
      type="text"
      placeholder="Rechercher un produit..."
      class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm mb-4
             focus:outline-none focus:ring-2 focus:ring-blue-600"
    />

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
          <tr>
            <th class="text-left px-4 py-3">Produit</th>
            <th class="text-left px-4 py-3">Référence</th>
            <th class="text-left px-4 py-3">Prix HT</th>
            <th class="text-left px-4 py-3">TVA</th>
            <th class="text-left px-4 py-3">Stock</th>
            <th class="text-left px-4 py-3">Statut</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-6 text-slate-400">Chargement...</td>
          </tr>
          <tr v-else-if="products.length === 0">
            <td colspan="6" class="text-center py-6 text-slate-400">Aucun produit</td>
          </tr>
          <tr v-for="product in products" :key="product.id" class="border-t border-slate-100">
            <td class="px-4 py-3">
              <div class="font-medium text-slate-900">{{ product.nom }}</div>
              <div class="text-slate-500 text-xs">{{ product.description }}</div>
            </td>
            <td class="px-4 py-3 text-slate-600">{{ product.reference }}</td>
            <td class="px-4 py-3 text-slate-600">{{ formatPrix(product.prix_ht) }} Ar</td>
            <td class="px-4 py-3 text-slate-600">{{ product.tva }}%</td>
            <td class="px-4 py-3">
                <span class="font-medium" :class="product.stock <= 5 ? 'text-red-600' : 'text-slate-700'">
                {{ product.stock }} {{ product.unite }}
                </span>
            </td>
            <td class="px-4 py-3">
              <span
                class="text-xs font-medium px-2.5 py-1 rounded-full"
                :class="product.statut === 'actif'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-slate-100 text-slate-500'"
              >
                {{ product.statut === 'actif' ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button @click="router.push(`/produits/${product.id}/modifier`)" class="text-blue-600 hover:text-blue-800">✏️</button>
                <button 
                    v-if="auth.can('stock.manage')"
                    @click="openStockModal(product)" 
                    class="text-slate-600 hover:text-slate-900" title="Mouvement de stock"
                >
                    📦
                </button>
              <button @click="remove(product)" class="text-red-500 hover:text-red-700">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
    <!-- ... juste avant </template> final -->
  <StockMovementModal
    v-if="showStockModal"
    :product="selectedProduct"
    @close="showStockModal = false"
    @saved="fetchProducts"
  />
</template>
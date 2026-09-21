<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { invoicesApi } from '@/api/invoices'
import { clientsApi } from '@/api/clients'
import { productsApi } from '@/api/products'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const clients = ref([])
const products = ref([])
const error = ref('')

const form = ref({
  client_id: '',
  date_emission: new Date().toISOString().slice(0, 10),
  date_echeance: '',
  items: [{ product_id: '', quantite: 1 }],
})

// Produits déjà sélectionnés (sauf sur la ligne courante)
function produitsDisponibles(indexActuel) {
  const idsUtilises = form.value.items
    .filter((_, i) => i !== indexActuel)
    .map(item => item.product_id)
    .filter(Boolean)

  return products.value.filter(p => !idsUtilises.includes(p.id) || p.id === form.value.items[indexActuel].product_id)
}

function ajouterLigne() {
  form.value.items.push({ product_id: '', quantite: 1 })
}
function supprimerLigne(index) {
  form.value.items.splice(index, 1)
}

function produitDe(id) {
  return products.value.find(p => p.id === id)
}

const lignesCalculees = computed(() =>
  form.value.items.map(item => {
    const p = produitDe(item.product_id)
    if (!p) return { ht: 0, tva: 0, ttc: 0 }
    const ht = item.quantite * p.prix_ht
    const tva = ht * p.tva / 100
    return { ht, tva, ttc: ht + tva }
  })
)

const totaux = computed(() => {
  const sousTotal = lignesCalculees.value.reduce((s, l) => s + l.ht, 0)
  const totalTva = lignesCalculees.value.reduce((s, l) => s + l.tva, 0)
  return { sousTotal, totalTva, totalTtc: sousTotal + totalTva }
})

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m)
}

onMounted(async () => {
  const [clientsRes, productsRes] = await Promise.all([
    clientsApi.list({ per_page: 100 }),
    productsApi.list({ per_page: 100 }),
  ])
  clients.value = clientsRes.data.data
  products.value = productsRes.data.data

  if (isEdit) {
    const { data } = await invoicesApi.get(route.params.id)
    form.value.client_id = data.client_id
    form.value.date_emission = data.date_emission
    form.value.date_echeance = data.date_echeance
    form.value.items = data.items.map(i => ({ product_id: i.product_id, quantite: i.quantite }))
  }
})

async function submit() {
  error.value = ''
  try {
    if (isEdit) {
      await invoicesApi.update(route.params.id, form.value)
    } else {
      await invoicesApi.create(form.value)
    }
    router.push('/factures')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  }
}
</script>

<template>
  <div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">
      {{ isEdit ? 'Modifier la facture' : 'Nouvelle facture' }}
    </h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 p-6 space-y-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Client *</label>
          <select v-model="form.client_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
            <option value="" disabled>Sélectionner un client</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom }}</option>
          </select>
        </div>
        <div></div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Date d'émission *</label>
          <input v-model="form.date_emission" type="date" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Date d'échéance *</label>
          <input v-model="form.date_echeance" type="date" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm" />
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium text-slate-900">Lignes de facture</h3>
          <button type="button" @click="ajouterLigne" class="text-blue-600 text-sm font-medium hover:text-blue-800">
            + Ajouter une ligne
          </button>
        </div>

        <div class="space-y-2">
          <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 items-center">
            <select v-model="item.product_id" required class="col-span-6 px-3 py-2 rounded-lg border border-slate-300 text-sm">
              <option value="" disabled>Produit</option>
              <option
                v-for="p in produitsDisponibles(i)"
                :key="p.id"
                :value="p.id"
              >
                {{ p.nom }} ({{ formatMontant(p.prix_ht) }} Ar)
              </option>
            </select>
            <input v-model.number="item.quantite" type="number" min="1" required
              class="col-span-2 px-3 py-2 rounded-lg border border-slate-300 text-sm" />
            <div class="col-span-3 text-sm text-slate-600 text-right">
              {{ formatMontant(lignesCalculees[i]?.ttc || 0) }} Ar
            </div>
            <button type="button" @click="supprimerLigne(i)" class="col-span-1 text-red-500 hover:text-red-700">🗑️</button>
          </div>
        </div>
      </div>

      <div class="border-t border-slate-200 pt-4 space-y-1 text-sm">
        <div class="flex justify-between text-slate-600">
          <span>Sous-total HT</span><span>{{ formatMontant(totaux.sousTotal) }} Ar</span>
        </div>
        <div class="flex justify-between text-slate-600">
          <span>TVA</span><span>{{ formatMontant(totaux.totalTva) }} Ar</span>
        </div>
        <div class="flex justify-between font-bold text-slate-900 text-base">
          <span>Total TTC</span><span>{{ formatMontant(totaux.totalTtc) }} Ar</span>
        </div>
      </div>

      <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg">{{ error }}</p>

      <div class="flex gap-3">
        <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
          Enregistrer en brouillon
        </button>
        <button type="button" @click="router.push('/factures')" class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-100 transition">
          Annuler
        </button>
      </div>
    </form>
  </div>
</template>
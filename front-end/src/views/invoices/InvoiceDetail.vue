<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { invoicesApi } from '@/api/invoices'

const route = useRoute()
const router = useRouter()
const invoice = ref(null)
const error = ref('')

async function fetch() {
  const { data } = await invoicesApi.get(route.params.id)
  invoice.value = data
}

async function envoyer() {
  try {
    await invoicesApi.envoyer(invoice.value.id)
    fetch()
  } catch (e) {
    error.value = e.response?.data?.message
  }
}

async function annuler() {
  if (!confirm('Annuler cette facture ?')) return
  await invoicesApi.annuler(invoice.value.id)
  fetch()
}

onMounted(fetch)
</script>

<template>
  <div v-if="invoice" class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-900">{{ invoice.numero || 'Brouillon' }}</h1>
      <div class="space-x-2">
        <button v-if="invoice.statut === 'brouillon'" @click="envoyer"
          class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700">
          Envoyer au client
        </button>
        <button v-if="invoice.statut !== 'payee' && invoice.statut !== 'annulee'" @click="annuler"
          class="bg-red-100 text-red-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-red-200">
          Annuler
        </button>
      </div>
    </div>

    <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg mb-4">{{ error }}</p>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
      <p class="text-slate-600 mb-1">Client : <strong>{{ invoice.client.nom }}</strong></p>
      <p class="text-slate-600 mb-4">Échéance : {{ invoice.date_echeance }}</p>

      <table class="w-full text-sm mb-4">
        <thead class="text-slate-500 text-xs uppercase border-b border-slate-200">
          <tr><th class="text-left py-2">Produit</th><th>Qté</th><th>PU HT</th><th>TVA</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in invoice.items" :key="item.id" class="border-b border-slate-100">
            <td class="py-2">{{ item.product.nom }}</td>
            <td class="text-center">{{ item.quantite }}</td>
            <td class="text-center">{{ item.prix_unitaire_ht }} Ar</td>
            <td class="text-center">{{ item.tva_taux }}%</td>
          </tr>
        </tbody>
      </table>

      <div class="text-right font-bold text-lg">Total TTC : {{ invoice.total_ttc }} Ar</div>
    </div>
  </div>
</template>
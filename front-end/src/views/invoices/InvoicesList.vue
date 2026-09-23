<script setup>
import { ref, onMounted, watch } from 'vue'
import { invoicesApi } from '@/api/invoices'
import ConfirmModal from '@/components/ConfirmModal.vue'
import PaymentModal from '@/components/PaymentModal.vue'

const invoices = ref([])
const loading = ref(false)
const search = ref('')
const statutFiltre = ref('')
const dateDebut = ref('')
const dateFin = ref('')

const selectedInvoice = ref(null)
const showModal = ref(false)
const showPaymentModal = ref(false)
const confirmState = ref(null)
const error = ref('')
const downloading = ref(false)

const statutStyle = {
  brouillon: 'bg-slate-100 text-slate-600',
  envoyee: 'bg-blue-100 text-blue-700',
  payee: 'bg-green-100 text-green-700',
  annulee: 'bg-red-100 text-red-700',
}
const statutLabel = {
  brouillon: 'Brouillon', envoyee: 'Envoyée', payee: 'Payée', annulee: 'Annulée',
}

const filtresStatut = [
  { value: '', label: 'Tous les statuts' },
  { value: 'brouillon', label: 'Brouillon' },
  { value: 'envoyee', label: 'Envoyée' },
  { value: 'en_retard', label: 'En retard' },
  { value: 'payee', label: 'Payée' },
  { value: 'annulee', label: 'Annulée' },
]

function estEnRetard(invoice) {
  return invoice.statut === 'envoyee' && new Date(invoice.date_echeance) < new Date()
}

async function fetchInvoices() {
  loading.value = true
  const { data } = await invoicesApi.list({
    search: search.value || undefined,
    statut: statutFiltre.value || undefined,
    date_debut: dateDebut.value || undefined,
    date_fin: dateFin.value || undefined,
  })
  invoices.value = data.data
  loading.value = false
}

let debounce
watch(search, () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchInvoices, 300)
})
watch([statutFiltre, dateDebut, dateFin], fetchInvoices)

function reinitialiserFiltres() {
  search.value = ''
  statutFiltre.value = ''
  dateDebut.value = ''
  dateFin.value = ''
}

async function openDetail(inv) {
  const { data } = await invoicesApi.get(inv.id)
  selectedInvoice.value = data
  showModal.value = true
  error.value = ''
}

function demanderEnvoi() {
  confirmState.value = {
    title: 'Envoyer la facture',
    message: `Envoyer ${selectedInvoice.value.numero || 'ce brouillon'} au client ? Le numéro sera figé et le stock sera décrémenté.`,
    confirmLabel: 'Envoyer',
    danger: false,
    action: async () => {
      try {
        await invoicesApi.envoyer(selectedInvoice.value.id)
        showModal.value = false
        fetchInvoices()
      } catch (e) {
        error.value = e.response?.data?.message
      }
    },
  }
}

function demanderAnnulation() {
  confirmState.value = {
    title: 'Annuler la facture',
    message: `Annuler ${selectedInvoice.value.numero} ? Le stock décrémenté sera restitué.`,
    confirmLabel: 'Annuler la facture',
    danger: true,
    action: async () => {
      await invoicesApi.annuler(selectedInvoice.value.id)
      showModal.value = false
      fetchInvoices()
    },
  }
}

function demanderSuppression(inv) {
  confirmState.value = {
    title: 'Supprimer le brouillon',
    message: `Supprimer définitivement ce brouillon ? Cette action est irréversible.`,
    confirmLabel: 'Supprimer',
    danger: true,
    action: async () => {
      await invoicesApi.remove(inv.id)
      showModal.value = false
      fetchInvoices()
    },
  }
}

async function executerConfirmation() {
  if (confirmState.value) {
    await confirmState.value.action()
    confirmState.value = null
  }
}

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m)
}

function formatDate(date){
  return new Date(date).toLocaleDateString('fr-FR')
}

function pourcentagePaye(invoice) {
  if (!invoice.total_ttc) return 0
  return Math.min(100, Math.round((invoice.montant_paye / invoice.total_ttc) * 100))
}

async function rafraichirDetail() {
  const { data } = await invoicesApi.get(selectedInvoice.value.id)
  selectedInvoice.value = data
  fetchInvoices() // pour mettre à jour le statut dans la liste aussi
}

async function telechargerPdf() {
  downloading.value = true
  try {
    await invoicesApi.downloadPdf(selectedInvoice.value.id, selectedInvoice.value.numero)
  } finally {
    downloading.value = false
  }
}

onMounted(fetchInvoices)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Factures</h1>
        <p class="text-sm text-slate-500">Gère toutes tes factures</p>
      </div>
      <router-link
        to="/factures/nouvelle"
        class="bg-blue-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-blue-700 transition"
      >
        + Nouvelle facture
      </router-link>
    </div>

    <!-- Barre de recherche + filtres -->
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Rechercher par numéro ou client..."
        class="flex-1 min-w-[240px] px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-600"
      />

      <select
        v-model="statutFiltre"
        class="px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600"
      >
        <option v-for="f in filtresStatut" :key="f.value" :value="f.value">{{ f.label }}</option>
      </select>

      <input
        v-model="dateDebut"
        type="date"
        class="px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm"
        title="Émise à partir du"
      />
      <span class="text-slate-400 text-sm">→</span>
      <input
        v-model="dateFin"
        type="date"
        class="px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm"
        title="Émise jusqu'au"
      />

      <button
        v-if="search || statutFiltre || dateDebut || dateFin"
        @click="reinitialiserFiltres"
        class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
      >
        Réinitialiser
      </button>

    </div>

     <!-- Tableau -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
          <tr>
            <th class="text-left px-4 py-3">Numéro</th>
            <th class="text-left px-4 py-3">Client</th>
            <th class="text-left px-4 py-3">Émission</th>
            <th class="text-left px-4 py-3">Échéance</th>
            <th class="text-left px-4 py-3">Total TTC</th>
            <th class="text-left px-4 py-3">Statut</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="text-center py-6 text-slate-400">Chargement...</td></tr>
          <tr v-else-if="invoices.length === 0"><td colspan="7" class="text-center py-6 text-slate-400">Aucune facture trouvée</td></tr>
          <tr v-for="inv in invoices" :key="inv.id" class="border-t border-slate-100">
            <td class="px-4 py-3 font-medium text-slate-900">{{ inv.numero || '—' }}</td>
            <td class="px-4 py-3 text-slate-600">{{ inv.client.nom }}</td>
            <td class="px-4 py-3 text-slate-600">{{ formatDate(inv.date_emission) }}</td>
            <td class="px-4 py-3 text-slate-600">{{ formatDate(inv.date_echeance) }}</td>
            <td class="px-4 py-3 text-slate-600">{{ formatMontant(inv.total_ttc) }} Ar</td>
            <td class="px-4 py-3 space-x-1">
              <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="statutStyle[inv.statut]">
                {{ statutLabel[inv.statut] }}
              </span>
              <span v-if="estEnRetard(inv)" class="text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">
                En retard
              </span>
              <span v-if="inv.partiellement_payee" class="text-xs font-medium px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700">
                Partielle
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-2">
              <button @click="openDetail(inv)" class="text-blue-600 hover:text-blue-800" title="Voir">👁️</button>
              <router-link
                v-if="inv.statut === 'brouillon'"
                :to="`/factures/${inv.id}/modifier`"
                class="text-slate-600 hover:text-slate-900"
                title="Modifier"
              >✏️</router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal détail -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-slate-900">{{ selectedInvoice.numero || 'Brouillon' }}</h2>
          <div class="flex items-center gap-2">
            <button
              v-if="selectedInvoice.numero"
              @click="telechargerPdf"
              :disabled="downloading"
              class="text-slate-600 hover:text-slate-900 disabled:opacity-50"
              title="Télécharger le PDF"
            >
              {{ downloading ? '⏳' : '⬇️' }}
            </button>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
          </div>
        </div>

        <p class="text-sm text-slate-600 mb-1">Client : <strong>{{ selectedInvoice.client.nom }}</strong></p>
        <p class="text-sm text-slate-600 mb-4">Émission : {{ formatDate(selectedInvoice.date_emission) }} — Échéance : {{ formatDate(selectedInvoice.date_echeance) }}</p>

        <table class="w-full text-sm mb-4">
          <thead class="text-slate-500 text-xs uppercase border-b border-slate-200">
            <tr><th class="text-left py-2">Produit</th><th>Qté</th><th>PU HT</th><th>TVA</th></tr>
          </thead>
          <tbody>
            <tr v-for="item in selectedInvoice.items" :key="item.id" class="border-b border-slate-100">
              <td class="py-2">{{ item.product.nom }}</td>
              <td class="text-center">{{ item.quantite }}</td>
              <td class="text-center">{{ item.prix_unitaire_ht }} Ar</td>
              <td class="text-center">{{ item.tva_taux }}%</td>
            </tr>
          </tbody>
        </table>

        <div class="text-right font-bold text-lg mb-4">Total TTC : {{ formatMontant(selectedInvoice.total_ttc) }} Ar</div>

         <!-- Section paiements — visible seulement si envoyée ou payée -->
        <div v-if="['envoyee', 'payee'].includes(selectedInvoice.statut)" class="border-t border-slate-200 pt-4 mt-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-medium text-slate-900">Paiements</h3>
            <button
              v-if="selectedInvoice.statut === 'envoyee'"
              @click="showPaymentModal = true"
              class="text-blue-600 text-sm font-medium hover:text-blue-800"
            >
              + Enregistrer un paiement
            </button>
          </div>

          <!-- Barre de progression -->
          <div class="mb-3">
            <div class="flex justify-between text-xs text-slate-500 mb-1">
              <span>{{ formatMontant(selectedInvoice.montant_paye) }} Ar payés</span>
              <span>{{ formatMontant(selectedInvoice.reste_a_payer) }} Ar restants</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-green-500 transition-all"
                :style="{ width: pourcentagePaye(selectedInvoice) + '%' }"
              ></div>
            </div>
            <span
              v-if="selectedInvoice.partiellement_payee"
              class="inline-block mt-2 text-xs font-medium px-2.5 py-1 rounded-full bg-orange-100 text-orange-700"
            >
              Partiellement payée
            </span>
          </div>

          <!-- Historique des paiements -->
          <div v-if="selectedInvoice.payments?.length" class="space-y-1">
            <div
              v-for="p in selectedInvoice.payments"
              :key="p.id"
              class="flex justify-between text-sm bg-slate-50 rounded-lg px-3 py-2"
            >
              <span>{{ p.date_paiement }} — {{ p.mode }}</span>
              <span class="font-medium">{{ formatMontant(p.montant) }} Ar</span>
            </div>
          </div>
        </div>

        <PaymentModal
          v-if="showPaymentModal"
          :invoice="selectedInvoice"
          @close="showPaymentModal = false"
          @saved="rafraichirDetail"
        />

        <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg mb-3">{{ error }}</p>

        <div class="flex gap-2 justify-end">
          <!-- Brouillon : Envoyer + Supprimer -->
          <template v-if="selectedInvoice.statut === 'brouillon'">
            <button @click="demanderSuppression(selectedInvoice)" class="bg-red-100 text-red-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-red-200">
              Supprimer
            </button>
            <button @click="demanderEnvoi" class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700">
              Envoyer au client
            </button>
          </template>

          <!-- Envoyée : Annuler seulement -->
          <button
            v-else-if="selectedInvoice.statut === 'envoyee'"
            @click="demanderAnnulation"
            class="bg-red-100 text-red-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-red-200"
          >
            Annuler la facture
          </button>

          <!-- Payée / Annulée : aucune action -->
        </div>
      </div>
    </div>

    <!-- Modal de confirmation générique -->
    <ConfirmModal
      v-if="confirmState"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-label="confirmState.confirmLabel"
      :danger="confirmState.danger"
      @confirm="executerConfirmation"
      @cancel="confirmState = null"
    />
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { invoicesApi } from '@/api/invoices'
import { clientsApi } from '@/api/clients'
import { productsApi } from '@/api/products'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import LineItemRow from '@/components/invoice/LineItemRow.vue'
import {
  UserIcon, CalendarIcon, ShoppingCartIcon, PlusIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const clients = ref([])
const products = ref([])
const loading = ref(false)
const saving = ref(false)
const fieldErrors = ref({})
const globalError = ref('')

const form = ref({
  client_id: '',
  date_emission: new Date().toISOString().slice(0, 10),
  date_echeance: '',
  items: [{ product_id: '', quantite: 1 }],
})

function ajouterLigne() {
  form.value.items.push({ product_id: '', quantite: 1 })
}
function supprimerLigne(index) {
  form.value.items.splice(index, 1)
}

// Calcule l'échéance à +30 jours par défaut dès que la date d'émission change,
// tant que l'utilisateur n'a pas déjà personnalisé l'échéance à la main
const echeanceModifieeManuellement = ref(false)
function onEmissionChange(value) {
  form.value.date_emission = value
  if (!echeanceModifieeManuellement.value) {
    const d = new Date(value)
    d.setDate(d.getDate() + 30)
    form.value.date_echeance = d.toISOString().slice(0, 10)
  }
}
function onEcheanceChange(value) {
  form.value.date_echeance = value
  echeanceModifieeManuellement.value = true
}

function produitsDisponiblesPour(indexActuel) {
  const idsUtilises = form.value.items
    .filter((_, i) => i !== indexActuel)
    .map(item => item.product_id)
    .filter(Boolean)
  return products.value.filter(
    p => !idsUtilises.includes(p.id) || p.id === form.value.items[indexActuel].product_id
  )
}

const totaux = computed(() => {
  let sousTotal = 0
  let totalTva = 0
  for (const item of form.value.items) {
    const p = products.value.find(pr => pr.id === item.product_id)
    if (!p) continue
    const ht = item.quantite * p.prix_ht
    sousTotal += ht
    totalTva += ht * p.tva / 100
  }
  return { sousTotal, totalTva, totalTtc: sousTotal + totalTva }
})

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m)
}

onMounted(async () => {
  loading.value = true
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
    echeanceModifieeManuellement.value = true // pas de recalcul auto en édition
    form.value.items = data.items.map(i => ({ product_id: i.product_id, quantite: i.quantite }))
  }
  loading.value = false
})

async function submit() {
  fieldErrors.value = {}
  globalError.value = ''
  saving.value = true
  try {
    if (isEdit) {
      await invoicesApi.update(route.params.id, form.value)
    } else {
      await invoicesApi.create(form.value)
    }
    router.push('/factures')
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = Object.fromEntries(
        Object.entries(e.response.data.errors || {}).map(([k, v]) => [k, v[0]])
      )
      globalError.value = e.response.data.message || 'Merci de corriger les champs en rouge.'
    } else {
      globalError.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w">
    <div class="flex items-center gap-4 mb-8">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shrink-0 shadow-lg shadow-blue-600/20">
        <ShoppingCartIcon class="w-7 h-7" />
      </div>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          {{ isEdit ? 'Modifier la facture' : 'Nouvelle facture' }}
        </h1>
        <p class="text-sm text-slate-500">
          {{ isEdit ? 'Cette facture est en brouillon, modifiable librement' : 'Enregistrée en brouillon, à envoyer ensuite' }}
        </p>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse" />
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <!-- Section : client & dates -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <UserIcon class="w-4 h-4 text-slate-400" /> Client & dates
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="client" label="Client" required :error="fieldErrors.client_id">
              <select
                id="client"
                v-model="form.client_id"
                class="w-full px-3.5 py-2.5 rounded-lg border text-sm bg-white transition-all
                       focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                :class="fieldErrors.client_id ? 'border-red-300' : 'border-slate-200 hover:border-slate-300'"
              >
                <option value="" disabled>Sélectionner un client</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom }}</option>
              </select>
            </FormField>
          </div>
          <FormField for="date_emission" label="Date d'émission" required :error="fieldErrors.date_emission">
            <TextInput
              id="date_emission" name="date_emission" type="date"
              :model-value="form.date_emission" @update:model-value="onEmissionChange"
              :error="!!fieldErrors.date_emission"
            >
              <template #icon><CalendarIcon class="w-4 h-4" /></template>
            </TextInput>
          </FormField>
          <FormField for="date_echeance" label="Date d'échéance" required :error="fieldErrors.date_echeance" hint="Calculée automatiquement à +30 jours, modifiable">
            <TextInput
              id="date_echeance" name="date_echeance" type="date"
              :model-value="form.date_echeance" @update:model-value="onEcheanceChange"
              :error="!!fieldErrors.date_echeance"
            >
              <template #icon><CalendarIcon class="w-4 h-4" /></template>
            </TextInput>
          </FormField>
        </div>
      </div>

      <!-- Section : lignes de facture -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
            <ShoppingCartIcon class="w-4 h-4 text-slate-400" /> Produits & services
          </h2>
          <button
            type="button"
            @click="ajouterLigne"
            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
          >
            <PlusIcon class="w-4 h-4" /> Ajouter une ligne
          </button>
        </div>

        <p v-if="fieldErrors.items" class="text-xs text-red-600 mb-2">{{ fieldErrors.items }}</p>

        <div v-if="form.items.length === 0" class="text-center py-8 text-sm text-slate-400">
          Aucune ligne — ajoute au moins un produit pour continuer
        </div>

        <div>
          <LineItemRow
            v-for="(item, i) in form.items"
            :key="i"
            v-model="form.items[i]"
            :products="products"
            :produits-disponibles="produitsDisponiblesPour(i)"
            :error="fieldErrors[`items.${i}.product_id`]"
            @remove="supprimerLigne(i)"
          />
        </div>

        <!-- Totaux -->
        <div class="border-t border-slate-200 mt-4 pt-4 space-y-1.5 text-sm">
          <div class="flex justify-between text-slate-500">
            <span>Sous-total HT</span><span>{{ formatMontant(totaux.sousTotal) }} Ar</span>
          </div>
          <div class="flex justify-between text-slate-500">
            <span>TVA</span><span>{{ formatMontant(totaux.totalTva) }} Ar</span>
          </div>
          <div class="flex justify-between font-bold text-slate-900 text-base pt-1">
            <span>Total TTC</span><span>{{ formatMontant(totaux.totalTtc) }} Ar</span>
          </div>
        </div>
      </div>

      <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
        {{ globalError }}
      </p>

      <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4 flex items-center gap-3">
        <Button type="submit" :loading="saving" :disabled="form.items.length === 0">
          {{ isEdit ? 'Enregistrer les modifications' : 'Créer le brouillon' }}
        </Button>
        <Button type="button" variant="secondary" @click="router.push('/factures')">
          Annuler
        </Button>
        <span class="ml-auto text-sm font-medium text-slate-500">
          Total : {{ formatMontant(totaux.totalTtc) }} Ar
        </span>
      </div>
    </form>
  </div>
</template>
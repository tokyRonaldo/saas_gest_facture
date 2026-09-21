<script setup>
import { ref } from 'vue'
import { paymentsApi } from '@/api/payments'

const props = defineProps(['invoice'])
const emit = defineEmits(['close', 'saved'])

const form = ref({
  montant: props.invoice.reste_a_payer,
  date_paiement: new Date().toISOString().slice(0, 10),
  mode: 'especes',
  reference: '',
  commentaire: '',
})
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await paymentsApi.create(props.invoice.id, form.value)
    emit('saved')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="emit('close')">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
      <h2 class="text-lg font-bold text-slate-900 mb-1">Enregistrer un paiement</h2>
      <p class="text-sm text-slate-500 mb-4">
        Reste à payer : {{ new Intl.NumberFormat('fr-FR').format(invoice.reste_a_payer) }} Ar
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Montant *</label>
          <input v-model.number="form.montant" type="number" step="0.01" min="0.01" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Date de paiement *</label>
          <input v-model="form.date_paiement" type="date" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Mode de paiement *</label>
          <select v-model="form.mode" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
            <option value="especes">Espèces</option>
            <option value="virement">Virement</option>
            <option value="cheque">Chèque</option>
            <option value="mobile_money">Mobile Money</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Référence</label>
          <input v-model="form.reference" placeholder="N° chèque, transaction..."
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Commentaire</label>
          <textarea v-model="form.commentaire" rows="2"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm"></textarea>
        </div>

        <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg">{{ error }}</p>

        <div class="flex gap-3">
          <button type="submit" :disabled="loading"
            class="flex-1 bg-blue-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
            {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
          <button type="button" @click="emit('close')"
            class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-100 transition">
            Annuler
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
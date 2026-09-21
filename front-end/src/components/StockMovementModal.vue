<script setup>
import { ref } from 'vue'
import { stockApi } from '@/api/stock'

const props = defineProps(['product'])
const emit = defineEmits(['close', 'saved'])

const form = ref({
  product_id: props.product.id,
  type: 'entree',
  quantite: 1,
  motif: 'achat',
  commentaire: '',
})
const error = ref('')
const loading = ref(false)

const motifsParType = {
  entree: [
    { value: 'achat', label: 'Achat fournisseur' },
    { value: 'retour', label: 'Retour client' },
    { value: 'ajustement', label: 'Ajustement inventaire' },
  ],
  sortie: [
    { value: 'vente', label: 'Vente' },
    { value: 'perte', label: 'Perte / casse' },
    { value: 'ajustement', label: 'Ajustement inventaire' },
  ],
}

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await stockApi.create(form.value)
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
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
      <h2 class="text-lg font-bold text-slate-900 mb-1">Mouvement de stock</h2>
      <p class="text-sm text-slate-500 mb-4">{{ product.nom }} — stock actuel : {{ product.stock }}</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-2">
          <button type="button" @click="form.type = 'entree'; form.motif = 'achat'"
            class="py-2 rounded-lg text-sm font-medium border transition"
            :class="form.type === 'entree' ? 'bg-green-600 text-white border-green-600' : 'border-slate-300 text-slate-600'">
            + Entrée
          </button>
          <button type="button" @click="form.type = 'sortie'; form.motif = 'vente'"
            class="py-2 rounded-lg text-sm font-medium border transition"
            :class="form.type === 'sortie' ? 'bg-red-600 text-white border-red-600' : 'border-slate-300 text-slate-600'">
            - Sortie
          </button>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Motif</label>
          <select v-model="form.motif" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
            <option v-for="m in motifsParType[form.type]" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Quantité</label>
          <input v-model.number="form.quantite" type="number" min="1" required
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
            {{ loading ? 'Enregistrement...' : 'Valider' }}
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
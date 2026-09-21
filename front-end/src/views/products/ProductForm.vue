<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productsApi } from '@/api/products'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const form = ref({
  nom: '', reference: '', description: '',
  prix_ht: '', tva: 19, unite: 'unité', statut: 'actif',
})
const error = ref('')

onMounted(async () => {
  if (isEdit) {
    const { data } = await productsApi.get(route.params.id)
    form.value = data
  }
})

async function submit() {
  error.value = ''
  try {
    if (isEdit) {
      await productsApi.update(route.params.id, form.value)
    } else {
      await productsApi.create(form.value)
    }
    router.push('/produits')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  }
}
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">
      {{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}
    </h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom *</label>
          <input v-model="form.nom" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Référence *</label>
          <input v-model="form.reference" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Unité</label>
          <input v-model="form.unite" placeholder="unité, kg, heure..."
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Prix HT *</label>
          <input v-model="form.prix_ht" type="number" step="0.01" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">TVA (%) *</label>
          <input v-model="form.tva" type="number" step="0.01" required
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
          <textarea v-model="form.description" rows="3"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"></textarea>
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Statut</label>
          <select v-model="form.statut"
            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>
        </div>
      </div>

      <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg">{{ error }}</p>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
          Enregistrer
        </button>
        <button type="button" @click="router.push('/produits')" class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-100 transition">
          Annuler
        </button>
      </div>
    </form>
  </div>
</template>
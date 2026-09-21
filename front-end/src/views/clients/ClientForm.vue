<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const form = ref({
  nom: '', email: '', telephone: '', adresse: '',
  ville: '', nif_stat: '', informations_complementaires: '',
})
const error = ref('')

onMounted(async () => {
  if (isEdit) {
    const { data } = await clientsApi.get(route.params.id)
    form.value = data
  }
})

async function submit() {
  error.value = ''
  try {
    if (isEdit) {
      await clientsApi.update(route.params.id, form.value)
    } else {
      await clientsApi.create(form.value)
    }
    router.push('/clients')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement'
  }
}
</script>

<template>
  <div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">
      {{ isEdit ? 'Modifier le client' : 'Nouveau client' }}
    </h1>

    <form @submit.prevent="submit" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Nom *</label>
          <input v-model="form.nom" required class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
          <input v-model="form.email" type="email" class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Téléphone</label>
          <input v-model="form.telephone" class="input" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Adresse</label>
          <input v-model="form.adresse" class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Ville</label>
          <input v-model="form.ville" class="input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">NIF/STAT</label>
          <input v-model="form.nif_stat" class="input" />
        </div>
        <div class="col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Informations complémentaires</label>
          <textarea v-model="form.informations_complementaires" rows="3" class="input"></textarea>
        </div>
      </div>

      <p v-if="error" class="text-red-600 text-sm bg-red-50 px-3 py-2 rounded-lg">{{ error }}</p>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-blue-700 transition">
          Enregistrer
        </button>
        <button type="button" @click="router.push('/clients')" class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-100 transition">
          Annuler
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
@reference "tailwindcss";

.input {
  @apply w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm
         focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition;
}
</style>
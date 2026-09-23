<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productsApi } from '@/api/products'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import {
  CubeIcon, TagIcon, CurrencyDollarIcon, DocumentTextIcon, ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const form = ref({
  nom: '', reference: '', description: '',
  prix_ht: '', tva: 19, unite: 'unité', statut: 'actif',
})
const fieldErrors = ref({})
const globalError = ref('')
const loading = ref(false)
const saving = ref(false)

// Aperçu du prix TTC en direct, pour aider à vérifier la TVA saisie
const prixTtcApercu = computed(() => {
  const ht = parseFloat(form.value.prix_ht) || 0
  const tva = parseFloat(form.value.tva) || 0
  return ht + (ht * tva / 100)
})

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m)
}

onMounted(async () => {
  if (isEdit) {
    loading.value = true
    const { data } = await productsApi.get(route.params.id)
    form.value = data
    loading.value = false
  }
})

async function submit() {
  fieldErrors.value = {}
  globalError.value = ''
  saving.value = true
  try {
    if (isEdit) {
      await productsApi.update(route.params.id, form.value)
    } else {
      await productsApi.create(form.value)
    }
    router.push('/produits')
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = Object.fromEntries(
        Object.entries(e.response.data.errors || {}).map(([k, v]) => [k, v[0]])
      )
      globalError.value = 'Merci de corriger les champs en rouge.'
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
        <CubeIcon class="w-7 h-7" />
      </div>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          {{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}
        </h1>
        <p class="text-sm text-slate-500">
          {{ isEdit ? 'Mets à jour les informations de ce produit' : 'Ajoute un produit ou service au catalogue' }}
        </p>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 4" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse" />
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <!-- Section : identification -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <TagIcon class="w-4 h-4 text-slate-400" /> Identification
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="nom" label="Nom du produit" required :error="fieldErrors.nom">
              <TextInput id="nom" name="nom" v-model="form.nom" placeholder="Ex. Ordinateur portable" :error="!!fieldErrors.nom">
                <template #icon><CubeIcon class="w-4 h-4" /></template>
              </TextInput>
            </FormField>
          </div>
          <FormField for="reference" label="Référence" required :error="fieldErrors.reference" hint="Doit être unique">
            <TextInput id="reference" name="reference" v-model="form.reference" placeholder="REF-001" :error="!!fieldErrors.reference" />
          </FormField>
          <FormField for="unite" label="Unité">
            <TextInput id="unite" name="unite" v-model="form.unite" placeholder="unité, kg, heure..." :error="!!fieldErrors.unite" />
          </FormField>
          <div class="col-span-2">
            <FormField for="description" label="Description" :error="fieldErrors.description">
              <TextInput id="description" name="description" v-model="form.description" type="textarea" placeholder="Détails visibles sur la fiche produit..." :error="!!fieldErrors.description">
                <template #icon><DocumentTextIcon class="w-4 h-4 mt-0.5" /></template>
              </TextInput>
            </FormField>
          </div>
        </div>
      </div>

      <!-- Section : tarification -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <CurrencyDollarIcon class="w-4 h-4 text-slate-400" /> Tarification
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <FormField for="prix_ht" label="Prix HT" required :error="fieldErrors.prix_ht">
            <TextInput id="prix_ht" name="prix_ht" v-model="form.prix_ht" type="number" placeholder="0.00" :error="!!fieldErrors.prix_ht">
              <template #icon><CurrencyDollarIcon class="w-4 h-4" /></template>
            </TextInput>
          </FormField>
          <FormField for="tva" label="TVA (%)" required :error="fieldErrors.tva">
            <TextInput id="tva" name="tva" v-model="form.tva" type="number" placeholder="19" :error="!!fieldErrors.tva" />
          </FormField>
        </div>

        <!-- Aperçu du prix TTC, mis à jour en direct -->
        <div class="mt-4 bg-blue-50 rounded-lg px-4 py-3 flex items-center justify-between">
          <span class="text-sm text-blue-700">Prix TTC estimé</span>
          <span class="text-lg font-bold text-blue-900">{{ formatMontant(prixTtcApercu) }} Ar</span>
        </div>
      </div>

      <!-- Section : statut -->
      <div class="bg-white w-1/2 rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <ArchiveBoxIcon class="w-4 h-4 text-slate-400" /> Statut
        </h2>
        <div class="flex gap-3">
          <button
            type="button"
            @click="form.statut = 'actif'"
            class="flex-1 py-2.5 rounded-lg text-sm font-medium border transition-all"
            :class="form.statut === 'actif'
              ? 'bg-green-600 text-white border-green-600'
              : 'border-slate-200 text-slate-500 hover:border-slate-300'"
          >
            Actif
          </button>
          <button
            type="button"
            @click="form.statut = 'inactif'"
            class="flex-1 py-2.5 rounded-lg text-sm font-medium border transition-all"
            :class="form.statut === 'inactif'
              ? 'bg-slate-600 text-white border-slate-600'
              : 'border-slate-200 text-slate-500 hover:border-slate-300'"
          >
            Inactif
          </button>
        </div>
      </div>

      <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
        {{ globalError }}
      </p>

      <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4 flex gap-3">
        <Button type="submit" :loading="saving">
          {{ isEdit ? 'Enregistrer les modifications' : 'Créer le produit' }}
        </Button>
        <Button type="button" variant="secondary" @click="router.push('/produits')">
          Annuler
        </Button>
      </div>
    </form>
  </div>
</template>
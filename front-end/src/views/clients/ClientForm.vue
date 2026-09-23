<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import {
  UserIcon, EnvelopeIcon, PhoneIcon, MapPinIcon,
  BuildingOfficeIcon, IdentificationIcon, DocumentTextIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const form = ref({
  nom: '', email: '', telephone: '', adresse: '',
  ville: '', nif_stat: '', informations_complementaires: '',
})
const fieldErrors = ref({})
const globalError = ref('')
const loading = ref(false)
const saving = ref(false)

const initiales = computed(() => {
  const n = form.value.nom.trim()
  if (!n) return '?'
  const parts = n.split(' ')
  return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : n.slice(0, 2).toUpperCase()
})

onMounted(async () => {
  if (isEdit) {
    loading.value = true
    const { data } = await clientsApi.get(route.params.id)
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
      await clientsApi.update(route.params.id, form.value)
    } else {
      await clientsApi.create(form.value)
    }
    router.push('/clients')
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
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-lg font-semibold shadow-lg shadow-blue-600/20 shrink-0 transition-all">
        {{ initiales }}
      </div>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          {{ isEdit ? 'Modifier le client' : 'Nouveau client' }}
        </h1>
        <p class="text-sm text-slate-500">
          {{ isEdit ? 'Mets à jour les informations de ce client' : 'Ajoute un client à ton carnet d\'adresses' }}
        </p>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 4" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse" />
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <UserIcon class="w-4 h-4 text-slate-400" /> Identité
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="nom" label="Nom du client" required :error="fieldErrors.nom">
              <TextInput id="nom" name="nom" autocomplete="organization" v-model="form.nom" placeholder="Ex. Société Rakoto SARL" :error="!!fieldErrors.nom">
                <template #icon><BuildingOfficeIcon class="w-4 h-4" /></template>
              </TextInput>
            </FormField>
          </div>
          <FormField for="email" label="Email" :error="fieldErrors.email">
            <TextInput id="email" name="email" autocomplete="email" v-model="form.email" type="email" placeholder="contact@societe.com" :error="!!fieldErrors.email">
              <template #icon><EnvelopeIcon class="w-4 h-4" /></template>
            </TextInput>
          </FormField>
          <FormField for="telephone" label="Téléphone" :error="fieldErrors.telephone">
            <TextInput id="telephone" name="telephone" autocomplete="tel" v-model="form.telephone" placeholder="034 00 000 00" :error="!!fieldErrors.telephone">
              <template #icon><PhoneIcon class="w-4 h-4" /></template>
            </TextInput>
          </FormField>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <MapPinIcon class="w-4 h-4 text-slate-400" /> Localisation
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="adresse" label="Adresse" :error="fieldErrors.adresse">
              <TextInput id="adresse" name="adresse" autocomplete="street-address" v-model="form.adresse" placeholder="Lot II M 12 Bis Antanimena" :error="!!fieldErrors.adresse" />
            </FormField>
          </div>
          <FormField for="ville" label="Ville" :error="fieldErrors.ville">
            <TextInput id="ville" name="ville" autocomplete="address-level2" v-model="form.ville" placeholder="Antananarivo" :error="!!fieldErrors.ville" />
          </FormField>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <IdentificationIcon class="w-4 h-4 text-slate-400" /> Informations complémentaires
        </h2>
        <div class="space-y-4">
          <FormField for="nif_stat" label="NIF / STAT" :error="fieldErrors.nif_stat" hint="Utilisé sur les factures officielles">
            <TextInput id="nif_stat" name="nif_stat" v-model="form.nif_stat" placeholder="Numéro d'identification fiscale" :error="!!fieldErrors.nif_stat" />
          </FormField>
          <FormField for="notes" label="Notes" :error="fieldErrors.informations_complementaires">
            <TextInput id="notes" name="notes" v-model="form.informations_complementaires" type="textarea" placeholder="Toute information utile sur ce client..." :error="!!fieldErrors.informations_complementaires">
              <template #icon><DocumentTextIcon class="w-4 h-4 mt-0.5" /></template>
            </TextInput>
          </FormField>
        </div>
      </div>

      <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
        {{ globalError }}
      </p>

      <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4 flex gap-3">
        <Button type="submit" :loading="saving">
          {{ isEdit ? 'Enregistrer les modifications' : 'Créer le client' }}
        </Button>
        <Button type="button" variant="secondary" @click="router.push('/clients')">
          Annuler
        </Button>
      </div>
    </form>
  </div>
</template>
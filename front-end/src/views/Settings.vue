<script setup>
import { ref, onMounted } from 'vue'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import { BuildingOfficeIcon, PhotoIcon, MapPinIcon, CreditCardIcon } from '@heroicons/vue/24/outline'
import { useSettingsStore } from '@/stores/settings'
import { settingsApi } from '@/api/settings'

const form = ref({
    nom_entreprise: '', email: '', telephone: '', adresse: '',
    ville: '', nif_stat: '', devise: 'Ar', conditions_paiement: '',
})
const settingsStore = useSettingsStore()
const logoPath = ref(null) 
const fieldErrors = ref({})
const globalError = ref('')
const successMessage = ref('')
const loading = ref(true)
const saving = ref(false)
const uploadingLogo = ref(false)
const fileInput = ref(null)

async function fetchSettings() {
  const { data } = await settingsApi.get()
  form.value = {
    nom_entreprise: data.nom_entreprise, email: data.email, telephone: data.telephone,
    adresse: data.adresse, ville: data.ville, nif_stat: data.nif_stat,
    devise: data.devise, conditions_paiement: data.conditions_paiement,
  }
  logoPath.value = data.logo_path
  loading.value = false
}

function declencherUpload() {
  fileInput.value.click()
}

async function onLogoChange(event) {
  const file = event.target.files[0]
  if (!file) return
  uploadingLogo.value = true
  try {
    const { data } = await settingsApi.uploadLogo(file)
    logoUrl.value = data.logo_url
    settingsStore.updateLocal(data) 
  } catch (e) {
    globalError.value = e.response?.data?.message || 'Erreur lors de l\'envoi du logo.'
  } finally {
    uploadingLogo.value = false
  }
}

async function submit() {
  fieldErrors.value = {}
  globalError.value = ''
  successMessage.value = ''
  saving.value = true
  try {
    await settingsApi.update(form.value)
    settingsStore.updateLocal({ ...settingsStore.settings, ...form.value }) // ← ajouté
    successMessage.value = 'Paramètres enregistrés avec succès.'
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

onMounted(fetchSettings)
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-slate-900">Paramètres</h1>
      <p class="text-sm text-slate-500">Informations de l'entreprise, visibles sur les factures</p>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 4" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse" />
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
      <!-- Section : logo -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <PhotoIcon class="w-4 h-4 text-slate-400" /> Logo
        </h2>
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="logoPath" :src="settingsApi.logoUrl(logoPath)" alt="Logo" class="w-full h-full object-contain" />
            <PhotoIcon v-else class="w-8 h-8 text-slate-300" />
          </div>
          <div>
            <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/svg+xml" class="hidden" @change="onLogoChange" />
            <Button type="button" variant="secondary" :loading="uploadingLogo" @click="declencherUpload">
              {{ logoUrl ? 'Changer le logo' : 'Ajouter un logo' }}
            </Button>
            <p class="text-xs text-slate-400 mt-2">PNG, JPG ou SVG — 2 Mo maximum</p>
          </div>
        </div>
      </div>

      <!-- Section : identité entreprise -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <BuildingOfficeIcon class="w-4 h-4 text-slate-400" /> Entreprise
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="nom_entreprise" label="Nom de l'entreprise" required :error="fieldErrors.nom_entreprise">
              <TextInput id="nom_entreprise" name="nom_entreprise" v-model="form.nom_entreprise" :error="!!fieldErrors.nom_entreprise" />
            </FormField>
          </div>
          <FormField for="email" label="Email" :error="fieldErrors.email">
            <TextInput id="email" name="email" type="email" v-model="form.email" :error="!!fieldErrors.email" />
          </FormField>
          <FormField for="telephone" label="Téléphone" :error="fieldErrors.telephone">
            <TextInput id="telephone" name="telephone" v-model="form.telephone" :error="!!fieldErrors.telephone" />
          </FormField>
          <FormField for="devise" label="Devise" required :error="fieldErrors.devise" hint="Ex. Ar, EUR, USD">
            <TextInput id="devise" name="devise" v-model="form.devise" :error="!!fieldErrors.devise" />
          </FormField>
          <FormField for="nif_stat" label="NIF / STAT" :error="fieldErrors.nif_stat">
            <TextInput id="nif_stat" name="nif_stat" v-model="form.nif_stat" :error="!!fieldErrors.nif_stat" />
          </FormField>
        </div>
      </div>

      <!-- Section : adresse -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <MapPinIcon class="w-4 h-4 text-slate-400" /> Adresse
        </h2>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <FormField for="adresse" label="Adresse" :error="fieldErrors.adresse">
              <TextInput id="adresse" name="adresse" v-model="form.adresse" :error="!!fieldErrors.adresse" />
            </FormField>
          </div>
          <FormField for="ville" label="Ville" :error="fieldErrors.ville">
            <TextInput id="ville" name="ville" v-model="form.ville" :error="!!fieldErrors.ville" />
          </FormField>
        </div>
      </div>

      <!-- Section : conditions de paiement (footer PDF) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-1 flex items-center gap-2">
          <CreditCardIcon class="w-4 h-4 text-slate-400" /> Conditions de paiement
        </h2>
        <p class="text-xs text-slate-500 mb-4">Affichées en pied de page sur les factures PDF</p>
        <FormField for="conditions_paiement" :error="fieldErrors.conditions_paiement">
          <TextInput id="conditions_paiement" name="conditions_paiement" type="textarea" v-model="form.conditions_paiement"
            placeholder="Ex. Paiement à réception, RIB : ..." :error="!!fieldErrors.conditions_paiement" />
        </FormField>
      </div>

      <p v-if="successMessage" class="text-sm text-green-700 bg-green-50 border border-green-100 px-4 py-3 rounded-lg">
        {{ successMessage }}
      </p>
      <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
        {{ globalError }}
      </p>

      <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4">
        <Button type="submit" :loading="saving">Enregistrer les paramètres</Button>
      </div>
    </form>
  </div>
</template>
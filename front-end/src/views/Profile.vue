<script setup>
import { ref, computed } from 'vue'
import { profileApi } from '@/api/profile'
import { useAuthStore } from '@/stores/auth'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import { UserIcon, EnvelopeIcon, LockClosedIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'

const auth = useAuthStore()

const form = ref({
  name: auth.user?.name || '',
  email: auth.user?.email || '',
  current_password: '',
  password: '',
  password_confirmation: '',
})
const fieldErrors = ref({})
const globalError = ref('')
const successMessage = ref('')
const saving = ref(false)

const roleLabel = { admin: 'Administrateur', user: 'Utilisateur', commercial: 'Commercial' }

const initiales = computed(() => {
  const n = form.value.name.trim()
  if (!n) return '?'
  const parts = n.split(' ')
  return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : n.slice(0, 2).toUpperCase()
})

async function submit() {
  fieldErrors.value = {}
  globalError.value = ''
  successMessage.value = ''
  saving.value = true
  try {
    const payload = { name: form.value.name, email: form.value.email }
    if (form.value.password) {
      payload.current_password = form.value.current_password
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    const { data } = await profileApi.update(payload)
    auth.user = data

    form.value.current_password = ''
    form.value.password = ''
    form.value.password_confirmation = ''
    successMessage.value = 'Profil mis à jour avec succès.'
  } catch (e) {
    if (e.response?.status === 422) {
      fieldErrors.value = Object.fromEntries(
        Object.entries(e.response.data.errors || {}).map(([k, v]) => [k, v[0]])
      )
      globalError.value = 'Merci de corriger les champs en rouge.'
    } else {
      globalError.value = e.response?.data?.message || 'Erreur lors de la mise à jour.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w">
    <div class="flex items-center gap-4 mb-8">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-lg font-semibold shrink-0 shadow-lg shadow-blue-600/20">
        {{ initiales }}
      </div>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Mon profil</h1>
        <p class="text-sm text-slate-500">Gère tes informations personnelles et ton mot de passe</p>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Section : identité -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
              <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <UserIcon class="w-4 h-4 text-slate-400" /> Identité
              </h2>
              <div class="space-y-4">
                <FormField for="name" label="Nom complet" required :error="fieldErrors.name">
                  <TextInput id="name" name="name" autocomplete="name" v-model="form.name" :error="!!fieldErrors.name">
                    <template #icon><UserIcon class="w-4 h-4" /></template>
                  </TextInput>
                </FormField>
                <FormField for="email" label="Email" required :error="fieldErrors.email">
                  <TextInput id="email" name="email" type="email" autocomplete="email" v-model="form.email" :error="!!fieldErrors.email">
                    <template #icon><EnvelopeIcon class="w-4 h-4" /></template>
                  </TextInput>
                </FormField>
                <div class="flex items-center gap-2 pt-1">
                  <ShieldCheckIcon class="w-4 h-4 text-slate-400" />
                  <span class="text-sm text-slate-500">Rôle : </span>
                  <span class="text-sm font-medium text-slate-700">{{ roleLabel[auth.user?.roles?.[0]] }}</span>
                </div>
              </div>
            </div>
      
            <!-- Section : mot de passe -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
              <h2 class="text-sm font-semibold text-slate-900 mb-1 flex items-center gap-2">
                <LockClosedIcon class="w-4 h-4 text-slate-400" /> Mot de passe
              </h2>
              <p class="text-xs text-slate-500 mb-4">Laisse ces champs vides si tu ne veux pas le changer</p>
              <div class="space-y-4">
                <FormField for="current_password" label="Mot de passe actuel" :error="fieldErrors.current_password">
                  <TextInput id="current_password" name="current_password" type="password" autocomplete="current-password" v-model="form.current_password" :error="!!fieldErrors.current_password" />
                </FormField>
                <div class="grid grid-cols-2 gap-4">
                  <FormField for="password" label="Nouveau mot de passe" :error="fieldErrors.password">
                    <TextInput id="password" name="password" type="password" autocomplete="new-password" v-model="form.password" :error="!!fieldErrors.password" />
                  </FormField>
                  <FormField for="password_confirmation" label="Confirmer le mot de passe">
                    <TextInput id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" v-model="form.password_confirmation" />
                  </FormField>
                </div>
              </div>
            </div>
        </div>

        <p v-if="successMessage" class="text-sm text-green-700 bg-green-50 border border-green-100 px-4 py-3 rounded-lg">
            {{ successMessage }}
        </p>
        <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
            {{ globalError }}
        </p>

        <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4">
            <Button type="submit" :loading="saving">Enregistrer les modifications</Button>
        </div>
    </form>
  </div>
</template>
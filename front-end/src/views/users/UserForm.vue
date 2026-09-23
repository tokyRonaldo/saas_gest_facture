<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usersApi } from '@/api/users'
import FormField from '@/components/ui/FormField.vue'
import TextInput from '@/components/ui/TextInput.vue'
import Button from '@/components/ui/Button.vue'
import { UserIcon, EnvelopeIcon, LockClosedIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const isEdit = !!route.params.id

const form = ref({ name: '', email: '', password: '', role: 'user' })
const fieldErrors = ref({})
const globalError = ref('')
const loading = ref(false)
const saving = ref(false)

const roles = [
  { value: 'admin', label: 'Administrateur', desc: 'Accès complet à toute l\'application' },
  { value: 'user', label: 'Utilisateur', desc: 'Gestion des clients, produits, factures et paiements' },
  { value: 'commercial', label: 'Commercial', desc: 'Création de factures, gestion du stock' },
]

const initiales = computed(() => {
  const n = form.value.name.trim()
  if (!n) return '?'
  const parts = n.split(' ')
  return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : n.slice(0, 2).toUpperCase()
})

onMounted(async () => {
  if (isEdit) {
    loading.value = true
    const { data } = await usersApi.get(route.params.id)
    form.value.name = data.name
    form.value.email = data.email
    form.value.role = data.roles[0]?.name || 'user'
    loading.value = false
  }
})

async function submit() {
  fieldErrors.value = {}
  globalError.value = ''
  saving.value = true
  try {
    if (isEdit) {
      await usersApi.update(route.params.id, form.value)
    } else {
      await usersApi.create(form.value)
    }
    router.push('/utilisateurs')
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
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-lg font-semibold shrink-0 shadow-lg shadow-blue-600/20">
        {{ initiales }}
      </div>
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          {{ isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
        </h1>
        <p class="text-sm text-slate-500">
          {{ isEdit ? 'Mets à jour les informations et le rôle' : 'Crée un accès pour un membre de l\'équipe' }}
        </p>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse" />
    </div>

    <form v-else @submit.prevent="submit" class="space-y-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Section : identité -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
              <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <UserIcon class="w-4 h-4 text-slate-400" /> Identité
              </h2>
              <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <FormField for="name" label="Nom complet" required :error="fieldErrors.name">
                    <TextInput id="name" name="name" autocomplete="name" v-model="form.name" placeholder="Ex. Marie Rakoto" :error="!!fieldErrors.name">
                      <template #icon><UserIcon class="w-4 h-4" /></template>
                    </TextInput>
                  </FormField>
                </div>
                <div class="col-span-2">
                  <FormField for="email" label="Email" required :error="fieldErrors.email">
                    <TextInput id="email" name="email" type="email" autocomplete="email" v-model="form.email" placeholder="marie@entreprise.com" :error="!!fieldErrors.email">
                      <template #icon><EnvelopeIcon class="w-4 h-4" /></template>
                    </TextInput>
                  </FormField>
                </div>
                <div class="col-span-2">
                  <FormField for="password" :label="isEdit ? 'Nouveau mot de passe' : 'Mot de passe'" :required="!isEdit" :error="fieldErrors.password" :hint="isEdit ? 'Laisser vide pour ne pas le changer' : 'Minimum 8 caractères'">
                    <TextInput id="password" name="password" type="password" autocomplete="new-password" v-model="form.password" placeholder="••••••••" :error="!!fieldErrors.password">
                      <template #icon><LockClosedIcon class="w-4 h-4" /></template>
                    </TextInput>
                  </FormField>
                </div>
              </div>
            </div>
      
            <!-- Section : rôle -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/50 p-6">
              <h2 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <ShieldCheckIcon class="w-4 h-4 text-slate-400" /> Rôle
              </h2>
              <div class="space-y-2">
                <label
                  v-for="r in roles"
                  :key="r.value"
                  class="flex items-start gap-3 p-3.5 rounded-lg border cursor-pointer transition-all"
                  :class="form.role === r.value ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-slate-300'"
                >
                  <input type="radio" :value="r.value" v-model="form.role" class="mt-1 accent-blue-600" />
                  <div>
                    <p class="text-sm font-medium text-slate-900">{{ r.label }}</p>
                    <p class="text-xs text-slate-500">{{ r.desc }}</p>
                  </div>
                </label>
              </div>
            </div>

        </div>      
        <p v-if="globalError" class="text-sm text-red-600 bg-red-50 border border-red-100 px-4 py-3 rounded-lg">
            {{ globalError }}
        </p>

        <div class="sticky bottom-0 bg-slate-50/80 backdrop-blur border-t border-slate-200 -mx-6 px-6 py-4 flex gap-3">
            <Button type="submit" :loading="saving">
            {{ isEdit ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' }}
            </Button>
            <Button type="button" variant="secondary" @click="router.push('/utilisateurs')">
            Annuler
            </Button>
        </div>
    </form>
  </div>
</template>
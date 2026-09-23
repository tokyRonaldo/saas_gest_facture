<!-- src/components/invoice/LineItemRow.vue -->
<script setup>
import { computed } from 'vue'
import { TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  modelValue: Object, // { product_id, quantite }
  products: Array,
  produitsDisponibles: Array,
  error: String,
})
const emit = defineEmits(['update:modelValue', 'remove'])

const produit = computed(() => props.products.find(p => p.id === props.modelValue.product_id))

const totalLigne = computed(() => {
  if (!produit.value) return 0
  const ht = props.modelValue.quantite * produit.value.prix_ht
  return ht + (ht * produit.value.tva / 100)
})

function formatMontant(m) {
  return new Intl.NumberFormat('fr-FR').format(m)
}

function update(field, value) {
  emit('update:modelValue', { ...props.modelValue, [field]: value })
}
</script>

<template>
  <div class="flex items-start gap-3 py-3" :class="{ 'border-b border-slate-100': true }">
    <div class="flex-1">
      <select
        :value="modelValue.product_id"
        @change="update('product_id', Number($event.target.value))"
        class="w-full px-3.5 py-2.5 rounded-lg border text-sm bg-white transition-all
               focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
        :class="error ? 'border-red-300' : 'border-slate-200 hover:border-slate-300'"
      >
        <option value="" disabled>Choisir un produit</option>
        <option v-for="p in produitsDisponibles" :key="p.id" :value="p.id">
          {{ p.nom }} — {{ formatMontant(p.prix_ht) }} Ar/{{ p.unite }}
        </option>
      </select>
      <p v-if="produit && produit.stock <= 5" class="text-xs text-orange-600 mt-1">
        Stock faible : {{ produit.stock }} {{ produit.unite }} restant(s)
      </p>
    </div>

    <input
      :value="modelValue.quantite"
      @input="update('quantite', Number($event.target.value))"
      type="number" min="1"
      class="w-20 px-3 py-2.5 rounded-lg border border-slate-200 text-sm text-center
             focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
    />

    <div class="w-32 text-right text-sm font-medium text-slate-700 pt-2.5">
      {{ formatMontant(totalLigne) }} Ar
    </div>

    <button
      type="button"
      @click="emit('remove')"
      class="text-slate-300 hover:text-red-500 transition-colors pt-2.5"
    >
      <TrashIcon class="w-4 h-4" />
    </button>
  </div>
</template>
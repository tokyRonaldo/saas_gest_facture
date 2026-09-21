<script setup>
defineProps({
  title: { type: String, default: 'Confirmer' },
  message: { type: String, required: true },
  confirmLabel: { type: String, default: 'Confirmer' },
  danger: { type: Boolean, default: false },
})
const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-[60]" @click.self="emit('cancel')">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm">
      <h3 class="text-lg font-bold text-slate-900 mb-2">{{ title }}</h3>
      <p class="text-sm text-slate-600 mb-6">{{ message }}</p>
      <div class="flex gap-3 justify-end">
        <button @click="emit('cancel')" class="text-slate-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-slate-100 transition">
          Annuler
        </button>
        <button
          @click="emit('confirm')"
          class="text-white text-sm font-medium px-4 py-2 rounded-lg transition"
          :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
        >
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
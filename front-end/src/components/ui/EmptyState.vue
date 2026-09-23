<script setup>
defineProps({ title: String, description: String })
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
      <slot name="icon" />
    </div>
    <h3 class="text-sm font-semibold text-slate-900 mb-1">{{ title }}</h3>
    <p class="text-sm text-slate-500 max-w-xs">{{ description }}</p>
    <div class="mt-4"><slot /></div>
  </div>
</template>
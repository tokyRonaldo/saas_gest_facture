<script setup>
defineProps(['label', 'value', 'icon', 'evolution', 'iconBg', 'iconColor'])
</script>

<template>
  <div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-start justify-between mb-3">
      <span class="text-sm text-slate-500">{{ label }}</span>
      <div class="w-9 h-9 rounded-lg flex items-center justify-center" :class="iconBg">
        <span :class="iconColor">{{ icon }}</span>
      </div>
    </div>
    <div class="text-2xl font-bold text-slate-900">{{ value }}</div>
    <div v-if="evolution !== null && evolution !== undefined" class="text-xs mt-1"
      :class="evolution >= 0 ? 'text-green-600' : 'text-red-600'">
      {{ evolution >= 0 ? '↗' : '↘' }} {{ Math.abs(evolution) }}% vs mois précédent
    </div>
  </div>
</template>
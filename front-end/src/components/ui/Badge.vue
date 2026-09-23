<script setup>
defineProps({
  color: { type: String, default: 'slate' }, // slate | blue | green | red | orange | yellow | purple
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full"
    :class="{
      slate: 'bg-slate-100 text-slate-600',
      blue: 'bg-blue-50 text-blue-700',
      green: 'bg-green-50 text-green-700',
      red: 'bg-red-50 text-red-700',
      orange: 'bg-orange-50 text-orange-700',
      yellow: 'bg-yellow-50 text-yellow-700',
      purple: 'bg-purple-50 text-purple-700',
    }[color]"
  >
    <span class="w-1.5 h-1.5 rounded-full" :class="{
      slate: 'bg-slate-400', blue: 'bg-blue-500', green: 'bg-green-500',
      red: 'bg-red-500', orange: 'bg-orange-500', yellow: 'bg-yellow-500', purple: 'bg-purple-500',
    }[color]" />
    <slot />
  </span>
</template>
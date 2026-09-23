<script setup>
defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | danger | ghost
  size: { type: String, default: 'md' }, // sm | md
  loading: { type: Boolean, default: false },
  type: { type: String, default: 'button' },
})
</script>

<template>
  <button
    :type="type"
    :disabled="loading"
    class="inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-all
           disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98]"
    :class="[
      size === 'sm' ? 'px-3 py-1.5 text-xs' : 'px-4 py-2.5 text-sm',
      {
        primary: 'bg-blue-600 text-white shadow-sm shadow-blue-600/20 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/30',
        secondary: 'bg-slate-100 text-slate-700 hover:bg-slate-200',
        danger: 'bg-red-50 text-red-600 hover:bg-red-100',
        ghost: 'text-slate-500 hover:bg-slate-100 hover:text-slate-900',
      }[variant],
    ]"
  >
    <svg v-if="loading" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg>
    <slot />
  </button>
</template>
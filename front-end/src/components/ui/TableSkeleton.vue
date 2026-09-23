<script setup>
defineProps({ rows: { type: Number, default: 5 }, cols: { type: Number, default: 4 } })
</script>

<template>
  <tr v-for="r in rows" :key="r" class="border-t border-slate-100">
    <td v-for="c in cols" :key="c" class="px-4 py-3.5">
      <div class="h-3.5 bg-slate-100 rounded animate-pulse" :style="{ width: Math.random() * 40 + 50 + '%' }" />
    </td>
  </tr>
</template>
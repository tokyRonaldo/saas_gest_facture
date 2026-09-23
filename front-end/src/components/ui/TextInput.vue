<script setup>
defineProps({
  modelValue: [String, Number],
  id: String,
  name: String,
  autocomplete: { type: String, default: 'off' },
  type: { type: String, default: 'text' },
  placeholder: String,
  error: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="relative">
    <div v-if="$slots.icon" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
      <slot name="icon" />
    </div>
    <component
      :is="type === 'textarea' ? 'textarea' : 'input'"
      :id="id"
      :name="name"
      :autocomplete="autocomplete"
      :type="type !== 'textarea' ? type : undefined"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :rows="type === 'textarea' ? 3 : undefined"
      class="w-full py-2.5 rounded-lg border text-sm bg-white transition-all
             placeholder:text-slate-400
             focus:outline-none focus:ring-4"
      :class="[
        $slots.icon ? 'pl-9 pr-3.5' : 'px-3.5',
        error
          ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
          : 'border-slate-200 focus:border-blue-500 focus:ring-blue-100 hover:border-slate-300',
      ]"
    />
  </div>
</template>
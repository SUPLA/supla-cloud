<script setup>
  import {computed} from 'vue';
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';
  import DropdownMenuContent from '@/common/gui/dropdown/dropdown-menu-content.vue';

  const props = defineProps({options: Array, value: [String, Number], disabled: Boolean});
  const emit = defineEmits(['input']);

  const modelValue = computed({
    get: () => props.value,
    set: (value) => emit('input', value),
  });
</script>

<template>
  <DropdownMenu :disabled="disabled">
    <DropdownMenuTrigger button>
      <slot v-if="$slots.button" name="button" :value="modelValue">{{ modelValue }}</slot>
      <slot v-else :value="modelValue">{{ modelValue }}</slot>
    </DropdownMenuTrigger>
    <DropdownMenuContent>
      <li v-for="option in options" :key="option">
        <a v-show="modelValue !== option" @click="modelValue = option">
          <slot :value="option">{{ option }}</slot>
        </a>
      </li>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup>
  import {computed} from "vue";
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";
  import DropdownMenuContent from "@/common/gui/dropdown/dropdown-menu-content.vue";

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
      <slot name="button" :value="modelValue" v-if="$slots.button">{{ modelValue }}</slot>
      <slot :value="modelValue" v-else>{{ modelValue }}</slot>
    </DropdownMenuTrigger>
    <DropdownMenuContent>
      <li v-for="option in options" :key="option">
        <a @click="modelValue = option" v-show="modelValue !== option">
          <slot :value="option">{{ option }}</slot>
        </a>
      </li>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

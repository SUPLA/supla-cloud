<script setup>
  import {computed} from "vue";
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";

  const props = defineProps({options: Array, value: [String, Number], disabled: Boolean});
    const emit = defineEmits(['input']);

    const modelValue = computed({
        get: () => props.value,
        set: (value) => emit('input', value),
    });
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger class="btn btn-default btn-block btn-wrapped" :disabled="disabled">
            <slot name="button" :value="modelValue">{{ modelValue }}</slot>
        </DropdownMenuTrigger>
        <ul class="dropdown-menu">
            <li v-for="option in options" :key="option">
                <a @click="modelValue = option" v-show="modelValue !== option">
                    <slot name="option" :option="option" :value="option">{{ option }}</slot>
                </a>
            </li>
        </ul>
    </DropdownMenu>
</template>

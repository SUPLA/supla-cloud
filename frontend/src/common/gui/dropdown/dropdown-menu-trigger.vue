<script setup>
  import {computed, inject, useTemplateRef} from 'vue';
  import {onClickOutside} from '@vueuse/core';

  const props = defineProps({button: Boolean});

  const classes = computed(() => (props.button ? 'btn btn-default btn-block btn-wrapped' : ''));

  const dropdownMenu = inject('dropdownMenu');

  const target = useTemplateRef('target');
  onClickOutside(target, () => dropdownMenu.hide());
</script>

<template>
  <a ref="target" class="dropdown-toggle" :class="[classes, {disabled: dropdownMenu.disabled}]" @click="dropdownMenu.toggle()">
    <slot />
    <span class="caret ml-2"></span>
  </a>
</template>

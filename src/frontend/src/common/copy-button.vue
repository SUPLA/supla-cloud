<template>
  <a :class="'copy-button btn btn-' + (copied ? copiedCssClass : defaultCssClass)" @click="copy(text)">
    <span v-if="copied">
      <i class="pe-7s-check"></i>
      {{ $t('copied') }}
    </span>
    <span v-else>
      <i class="pe-7s-copy-file"></i>
      {{ copyTextI18n ? $t(copyTextI18n) : $t('copy') }}
    </span>
  </a>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import {useClipboard} from '@vueuse/core';

  const props = defineProps(['text', 'copiedClass', 'defaultClass', 'copyTextI18n']);

  const source = ref('');
  const {copy, copied} = useClipboard({source});

  const copiedCssClass = computed(() => props.copiedClass || 'green');
  const defaultCssClass = computed(() => props.defaultClass || 'white');
</script>

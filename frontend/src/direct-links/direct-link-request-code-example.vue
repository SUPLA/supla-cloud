<script setup>
  import {storeToRefs} from 'pinia';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store.js';
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import {computed} from 'vue';
  import {useDirectLinkExamples} from '@/direct-links/direct-link-examples.js';

  const props = defineProps({
    mode: String,
    directLink: Object,
  });

  const {config} = storeToRefs(useFrontendConfigStore());
  const {slugs} = storeToRefs(useDirectLinksStore());

  const url = computed(() => {
    return `${config.value.suplaUrl}/direct/${props.directLink.id}`;
  });

  const slug = computed(() => slugs.value[props.directLink.id] ?? 'TOKEN');

  const {snippets} = useDirectLinkExamples();

  const code = computed(() => {
    return snippets[props.mode](url.value, {code: slug.value, action: 'read'});
  });
</script>

<template>
  <textarea :value="code" class="code-block" readonly></textarea>
</template>

<style scoped>
  .code-block {
    height: 100px;
    overflow-y: auto;
    width: 100%;
    font-family: monospace;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f5f5f5;
    resize: vertical;
  }
</style>

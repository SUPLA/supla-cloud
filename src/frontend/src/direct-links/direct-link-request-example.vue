<script setup>
  import {storeToRefs} from 'pinia';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store.js';
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import {computed} from 'vue';
  import CopyButton from '@/common/copy-button.vue';
  import {useDirectLinkExamples} from '@/direct-links/direct-link-examples.js';
  import {useSubject} from '@/stores/subjects-store.js';
  import {urlParams} from '@/common/utils';

  const props = defineProps({
    mode: String,
    action: Object,
    directLink: Object,
  });

  const {config} = storeToRefs(useFrontendConfigStore());
  const {slugs} = storeToRefs(useDirectLinksStore());

  const urlBeginning = computed(() => {
    return `${config.value.suplaUrl}/direct/${props.directLink.id}`;
  });

  const {subject} = useSubject(props.directLink);

  const slug = computed(() => slugs.value[props.directLink.id] ?? 'TOKEN');

  const fullLink = computed(() => {
    const queryString = Object.keys(actionParamsJson.value).length > 0 ? '?' + urlParams(actionParamsJson.value) : '';
    return `${urlBeginning.value}/${slug.value}/${props.action.nameSlug}${queryString}`;
  });

  const {exampleParams, snippets, exampleModeLabels} = useDirectLinkExamples();

  const actionParams = computed(() => exampleParams[props.action.id]?.(subject.value) ?? []);
  const actionParamsJson = computed(() => actionParams.value.reduce((acc, param) => ({...acc, [param.name]: param.example}), {}));
  const exampleJsonRequest = computed(() => ({code: slug.value, action: props.action.nameSlug, ...actionParamsJson.value}));
  const snippet = computed(() => snippets[props.mode]?.(urlBeginning.value, exampleJsonRequest.value) ?? '');
  const isAllowed = computed(() => props.directLink.allowedActions.includes(props.action.name));
</script>

<template>
  <div v-if="mode === 'link'" class="d-flex align-items-center">
    <div class="flex-grow-1">
      <a :href="fullLink" target="_blank" v-if="isAllowed && slug !== 'TOKEN'" class="text-monospace link">{{ fullLink }}</a>
      <span v-else class="link text-monospace"> {{ fullLink }}</span>
    </div>
    <CopyButton :text="fullLink" />
  </div>
  <div v-else>
    <div class="d-flex">
      <div class="flex-grow-1 mr-3">
        <p class="small" v-if="actionParams.length">{{ $t('Example payload') }}</p>
        <p class="small" v-else>{{ $t('Payload') }}</p>
        <pre class="code-block"><code>{{ JSON.stringify(exampleJsonRequest) }}</code></pre>
      </div>
      <div>
        <CopyButton :text="snippet" :copy-text-i18n="$t('copy') + ' (' + $t(exampleModeLabels[mode]) + ')'" class="d-block mb-2" />
        <CopyButton :text="JSON.stringify(exampleJsonRequest)" :copy-text-i18n="$t('copy') + ' (payload)'" class="d-block" />
      </div>
    </div>
  </div>
  <div v-if="actionParams.length" class="mt-2">
    <p class="small">{{ $t('Parameters') }}</p>
    <div v-for="param in actionParams" :key="param.name">
      <code>{{ param.name }}</code> - {{ $t(param.description, {example: param.example}) }}
    </div>
  </div>
</template>

<style scoped>
  .link {
    white-space: wrap;
  }
</style>

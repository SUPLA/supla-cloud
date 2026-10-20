<template>
  <div>
    <div class="container text-center">
      <h1 class="nocapitalize">{{ $t(headerI18n) }}</h1>
      <i :class="[icon, 'error-page-icon']" style="font-size: 160px"></i>
      <div class="my-3">
        <h5>{{ $t(messageI18n, {statusCode}) }}</h5>
      </div>
      <div v-if="guruMeditation">
        <button v-if="!guruMeditationVisible" type="button" class="btn btn-link" @click="guruMeditationVisible = true">
          Guru Meditation <i class="pe-7s-help1"></i>
        </button>
        <div v-if="guruMeditationVisible" class="well" style="max-width: 600px; margin: 0 auto">
          <h3 class="no-margin-top">{{ $t('Guru Meditation') }}</h3>
          <pre><code>{{ guruMeditation }}</code></pre>
        </div>
      </div>
      <div class="my-3">
        <a href="/">{{ $t('Start from the homepage.') }}</a>
      </div>
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
  import {ref} from 'vue';

  defineProps(['headerI18n', 'messageI18n', 'icon', 'guruMeditation', 'statusCode']);

  const guruMeditationVisible = ref(false);
</script>

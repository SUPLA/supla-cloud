<template>
  <div>
    <div class="container text-center">
      <h1 class="nocapitalize">{{ $t(headerI18n) }}</h1>
      <i :class="[icon, 'error-page-icon']" style="font-size: 160px"></i>
      <div class="my-3">
        <h5 v-if="guruMeditation">{{ guruMeditation }}</h5>
        <h5 v-else>{{ $t(messageI18n, {statusCode}) }}</h5>
      </div>
      <div class="my-3">
        <router-link to="/">{{ $t('Start from the homepage.') }}</router-link>
      </div>
      <slot></slot>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['headerI18n', 'messageI18n', 'icon', 'guruMeditation', 'statusCode'],
  };
</script>

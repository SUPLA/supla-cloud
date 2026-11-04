<template>
  <span v-if="count > 0" class="label label-warning ml-1 requests-pending-indicator" :title="$t('Pending decisions need your attention.')">
    {{ count }}
    <span class="sr-only">{{ $t('Pending decisions need your attention.') }}</span>
  </span>
</template>

<script setup>
  defineOptions({compatConfig: {MODE: 3}});
  defineProps({count: Number});
</script>

<style scoped>
  .requests-pending-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    height: 1.6em;
    min-width: 1.6em;
    padding: 0 0.3em;
    /* Keep the badge from increasing the surrounding text line's height. */
    margin-block: -0.3em;
    border-radius: 999px;
    line-height: 1;
    vertical-align: middle;
  }
</style>

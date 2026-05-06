<template>
  <div v-tooltip="$t('Channel with action triggers')" class="action-trigger-indicator">
    <div class="action-trigger-indicator-button"></div>
  </div>
</template>

<script>
  export default {};
</script>

<style lang="scss">
  @use 'sass:math';
  @use '../../styles/variables' as *;

  $size: 16px;

  .action-trigger-indicator {
    width: $size;
    height: $size;
    border-radius: math.div($size, 4);
    border: 1px solid $supla-grey-dark;
    .action-trigger-indicator-button {
      border: 1px solid $supla-grey-dark;
      width: math.div($size, 2);
      height: math.div($size, 2);
      border-radius: 50%;
      margin-top: math.div($size, 4) - 1px;
      margin-left: math.div($size, 4) - 1px;
    }
  }
</style>

<template>
  <div class="btn btn-load">
    <button-loading-dots></button-loading-dots>
  </div>
</template>

<script>
  import ButtonLoadingDots from './button-loading-dots.vue';

  export default {
    components: {ButtonLoadingDots},
  };
</script>

<style lang="scss">
  .btn-load {
    border-color: #fff;
    height: 44px;
    padding: 12px 20px 0;
    cursor: no-drop;
  }
</style>

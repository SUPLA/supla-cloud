<template>
  <div class="flip-container">
    <div v-if="!flipped" class="front">
      <slot name="front"></slot>
    </div>
    <div v-else class="back">
      <slot name="back"></slot>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['flipped'],
  };
</script>

<style lang="scss">
  .flip-container {
    &,
    .front,
    .back {
      width: 100%;
      height: 100%;
    }
  }
</style>

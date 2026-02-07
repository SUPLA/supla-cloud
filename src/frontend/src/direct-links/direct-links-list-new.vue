<template>
  <div class="direct-links-grid px-3" :class="{narrow: items.length < 9}">
    <DirectLinkTile v-for="item in items" :key="item.id" :model="item" />
  </div>
</template>

<script setup>
  import DirectLinkTile from './direct-link-tile.vue';

  defineProps({items: Array});
</script>

<style scoped>
  .direct-links-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1rem;
  }

  .direct-links-grid.narrow {
    margin: 0 auto;
    max-width: 1170px;
  }

  @media (min-width: 576px) {
    .direct-links-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 768px) {
    .direct-links-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (min-width: 992px) {
    .direct-links-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (min-width: 1200px) {
    .direct-links-grid {
      grid-template-columns: repeat(6, 1fr);
    }
    .direct-links-grid.narrow {
      grid-template-columns: repeat(4, 1fr);
    }
  }
</style>

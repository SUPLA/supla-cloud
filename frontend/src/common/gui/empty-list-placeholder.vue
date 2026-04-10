<template>
  <div class="text-center empty-list-placeholder">
    <div>
      <i class="pe-7s-paint-bucket"></i>
      <span class="empty">{{ $t('Empty!') }}</span>
    </div>
    <div class="mt-3" v-if="total > 0">
      <a @click="$emit('clear')" class="btn btn-white">
        <fa :icon="faFilterCircleXmark" />
        {{ $t('Clear filters') }}
      </a>
    </div>
  </div>
</template>

<script setup>
  import {faFilterCircleXmark} from '@fortawesome/free-solid-svg-icons';

  defineProps({total: Number});

  defineEmits(['clear']);
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<style lang="scss">
  .empty-list-placeholder {
    i {
      display: block;
      font-size: 4em;
      margin-top: 15px;
    }
    .empty {
      font-size: 2em;
    }
    &.inline {
      i {
        display: none;
      }
      .empty {
        font-size: 1em;
        font-style: italic;
      }
    }
  }
</style>

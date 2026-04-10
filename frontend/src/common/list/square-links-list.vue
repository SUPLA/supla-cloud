<template>
  <div class="container mb-3" v-if="filtersDef">
    <ListFilters v-model="filters" :def="filtersDef" />
  </div>
  <div v-if="items.length > 0" class="square-links-list-container">
    <TransitionGroup
      :name="animate ? 'list' : ''"
      tag="div"
      class="square-links-list px-3"
      :class="{narrow: items.length < 9}"
      @before-leave="beforeLeave"
      @after-leave="afterLeave"
    >
      <div v-for="item in items" :key="item.id">
        <slot name="item" :item="item" />
      </div>
    </TransitionGroup>
  </div>
  <EmptyListPlaceholder v-else :total="total" @clear="filters = {...defaultFilters}" />
</template>

<script setup>
  import {nextTick, onMounted, ref} from 'vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import ListFilters from '@/common/list/list-filters.vue';

  defineProps({
    items: Array,
    filtersDef: Object,
    total: Number,
  });

  const defaultFilters = ref({});

  const filters = defineModel('filters');

  onMounted(async () => {
    defaultFilters.value = {...filters.value};
    await nextTick();
    animate.value = true;
  });

  const animate = ref(false);

  const beforeLeave = (el) => {
    const {top, left, width, height} = el.getBoundingClientRect();
    const parentRect = el.parentElement.getBoundingClientRect();
    el.style.left = `${left - parentRect.left}px`;
    el.style.top = `${top - parentRect.top}px`;
    el.style.width = `${width}px`;
    el.style.height = `${height}px`;
    el.classList.add('list-leave-absolute');
  };

  const afterLeave = (el) => {
    el.classList.remove('list-leave-absolute');
    el.style.left = '';
    el.style.top = '';
    el.style.width = '';
    el.style.height = '';
  };
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<style scoped>
  .square-links-list-container {
    padding: 0 1em;
  }

  .square-links-list {
    position: relative;
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1rem;
  }

  .square-links-list.narrow {
    margin: 0 auto;
    max-width: 1170px;
  }

  .list-move,
  .list-enter-active,
  .list-leave-active {
    transition: transform 0.3s ease;
  }

  .list-enter-from,
  .list-leave-to {
    opacity: 0;
    transform: scale(0.2);
  }

  .list-leave-active.list-leave-absolute {
    position: absolute;
  }

  @media (min-width: 576px) {
    .square-links-list {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 768px) {
    .square-links-list {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (min-width: 992px) {
    .square-links-list {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (min-width: 1200px) {
    .square-links-list {
      grid-template-columns: repeat(5, 1fr);
    }
    .square-links-list.narrow {
      grid-template-columns: repeat(4, 1fr);
    }
    .square-links-list-container {
      padding: 0 3em;
    }
  }

  @media (min-width: 1500px) {
    .square-links-list {
      grid-template-columns: repeat(6, 1fr);
    }
  }

  @media (min-width: 1800px) {
    .square-links-list {
      grid-template-columns: repeat(7, 1fr);
    }
  }

  @media (min-width: 2100px) {
    .square-links-list {
      grid-template-columns: repeat(8, 1fr);
    }
  }

  @media (min-width: 2400px) {
    .square-links-list {
      grid-template-columns: repeat(9, 1fr);
    }
  }
</style>

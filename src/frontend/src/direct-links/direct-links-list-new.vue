<template>
  <div class="container">
    <ListFilters v-model="filters" :def="filtersDef" />
  </div>
  <div v-if="filteredItems.length > 0">
    <TransitionGroup name="list" tag="div" class="direct-links-grid px-3" :class="{narrow: filteredItems.length < 9}">
      <DirectLinkTile v-for="item in filteredItems" :key="item.id" :model="item" />
    </TransitionGroup>
  </div>
  <EmptyListPlaceholder v-else :total="items.length" @clear="filters = {...defaultFilters}" />
</template>

<script setup>
  import DirectLinkTile from './direct-link-tile.vue';
  import ListFilters from '@/direct-links/list-filters.vue';
  import {computed, ref} from 'vue';
  import latinize from 'latinize';
  import {DateTime} from 'luxon';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';

  const props = defineProps({items: Array});

  const defaultFilters = {
    sort: 'caption',
    active: 'all',
    search: '',
  };

  const filters = ref({...defaultFilters});

  const filtersDef = ref({
    sort: [
      {label: 'A-Z', value: 'caption'},
      {label: 'ID', value: 'id'},
      {label: 'Last used', value: 'lastUsed'},
    ],
    active: [
      {label: 'All', value: 'all'},
      {label: 'Active', value: true},
      {label: 'Inactive', value: false},
    ],
  });

  const filteredItems = computed(() => {
    const f = filters.value;
    let arr = props.items;
    if (f.active !== 'all') {
      arr = arr.filter((i) => i.active === f.active);
    }
    const q = latinize(f.search.trim().toLowerCase());
    if (q) {
      const searchString = (dl) => latinize([dl.id, dl.caption].join(' ')).toLowerCase();
      arr = arr.filter((i) => searchString(i).includes(q));
    }
    const out = [...arr];
    if (f.sort === 'caption') {
      out.sort((a, b) => (a.caption ?? '').localeCompare(b.caption ?? ''));
    } else if (f.sort === 'lastUsed') {
      out.sort((a, b) => DateTime.fromISO(b.lastUsed || '2000-01-01T00:00:00').diff(DateTime.fromISO(a.lastUsed || '2000-01-01T00:00:00')).milliseconds);
    } else {
      out.sort((a, b) => +a.id - +b.id);
    }
    return out;
  });
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

  .list-move,
  .list-enter-active,
  .list-leave-active {
    transition: all 0.3s ease;
  }

  .list-enter-from,
  .list-leave-to {
    opacity: 0;
    transform: scale(0.2);
  }

  .list-leave-active {
    position: absolute;
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

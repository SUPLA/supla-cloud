<template>
  <SquareLinksList :filters-def="filtersDef" :items="filteredItems" :total="items.length" v-model:filters="filters">
    <template #item="{item}">
      <DirectLinkTile :model="item" />
    </template>
  </SquareLinksList>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import latinize from 'latinize';
  import {DateTime} from 'luxon';
  import SquareLinksList from '@/direct-links/square-links-list.vue';
  import DirectLinkTile from '@/direct-links/direct-link-tile.vue';

  const props = defineProps({items: Array});

  const filters = ref({
    sort: 'caption',
    active: 'all',
    search: '',
  });

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

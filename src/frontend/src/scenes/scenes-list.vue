<template>
  <SquareLinksList :filters-def="filtersDef" :items="filteredItems" :total="items.length" v-model:filters="filters">
    <template #item="{item}">
      <SceneTile :model="item" />
    </template>
  </SquareLinksList>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import latinize from 'latinize';
  import SquareLinksList from '@/common/list/square-links-list.vue';
  import SceneTile from '@/scenes/scene-tile.vue';

  const props = defineProps({items: Array});

  const filters = ref({
    sort: 'caption',
    enabled: 'all',
    search: '',
  });

  const filtersDef = ref({
    sort: [
      {label: 'A-Z', value: 'caption'},
      {label: 'ID', value: 'id'},
    ],
    enabled: [
      {label: 'All', value: 'all'},
      {label: 'Enabled', value: true},
      {label: 'Disabled', value: false},
    ],
  });

  const filteredItems = computed(() => {
    const f = filters.value;
    let arr = props.items;
    if (f.enabled !== 'all') {
      arr = arr.filter((i) => i.enabled === f.enabled);
    }
    const q = latinize(f.search.trim().toLowerCase());
    if (q) {
      const searchString = (item) => latinize([item.id, item.caption].join(' ')).toLowerCase();
      arr = arr.filter((i) => searchString(i).includes(q));
    }
    const out = [...arr];
    if (f.sort === 'caption') {
      out.sort((a, b) => (a.caption ?? '').localeCompare(b.caption ?? ''));
    } else {
      out.sort((a, b) => +a.id - +b.id);
    }
    return out;
  });
</script>

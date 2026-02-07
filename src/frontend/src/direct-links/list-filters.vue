<template>
  <div class="grid-filters">
    <BtnFilters v-for="btnKey in btnKeys" :key="btnKey" :filters="def[btnKey]" :id="btnKey + 'Filters'" @input="setField(btnKey, $event)" />
    <input
      v-if="hasSearch"
      :value="model.search || ''"
      type="text"
      class="form-control"
      :placeholder="$t('Search')"
      @input="setField('search', $event.target.value)"
    />
  </div>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';
  import BtnFilters from '@/common/btn-filters.vue';

  const props = defineProps({def: Object});
  const model = defineModel({type: Object, required: true});

  const hasSearch = computed(() => model.value && Object.prototype.hasOwnProperty.call(model.value, 'search'));

  const btnKeys = computed(() => Object.keys(props.def));

  function setField(key, value) {
    model.value = {...(model.value || {}), [key]: value};
  }
</script>

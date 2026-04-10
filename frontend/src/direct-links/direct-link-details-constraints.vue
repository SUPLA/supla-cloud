<template>
  <h2>{{ $t('Constraints') }}</h2>
  <div class="row">
    <div class="col-sm-6">
      <div class="details-page-block">
        <h3 class="text-center">{{ $t('Working period') }}</h3>
        <DateRangePicker v-model="activeDateRange" />
      </div>
    </div>
    <div class="col-sm-6">
      <div class="details-page-block">
        <h3 class="text-center">{{ $t('Execution limit') }}</h3>
        <div class="executions-limit">
          {{ executionsLimit }}
        </div>
        <div class="text-center">
          <a class="btn btn-default mx-1" @click="setExecutionsLimit(undefined)">{{ $t('No limit') }}</a>
          <a class="btn btn-default mx-1" @click="setExecutionsLimit(1)">1</a>
          <a class="btn btn-default mx-1" @click="setExecutionsLimit(2)">2</a>
          <a class="btn btn-default mx-1" @click="setExecutionsLimit(10)">10</a>
          <a class="btn btn-default mx-1" @click="setExecutionsLimit(100)">100</a>
          <a :class="'btn btn-default mx-1 ' + (choosingCustomLimit ? 'active' : '')" @click="choosingCustomLimit = !choosingCustomLimit">{{ $t('Custom') }}</a>
        </div>
        <div v-if="choosingCustomLimit">
          <div class="form-group"></div>
          <label>{{ $t('Custom execution limit') }}</label>
          <input v-model="executionsLimit" class="form-control" type="number" min="0" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import {defineModel, ref} from 'vue';
  import DateRangePicker from '@/activity/date-range-picker.vue';

  const activeDateRange = defineModel('activeDateRange');
  const executionsLimit = defineModel('executionsLimit');

  const choosingCustomLimit = ref(false);

  function setExecutionsLimit(value) {
    executionsLimit.value = value;
    choosingCustomLimit.value = false;
  }
</script>

<style scoped>
  .executions-limit {
    font-size: 3em;
    font-weight: bold;
    color: var(--supla-orange);
    text-align: center;
    margin-bottom: 10px;
  }
</style>

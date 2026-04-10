<template>
  <div>
    <div class="form-group">
      <div class="btn-group btn-group-justified">
        <div class="btn-group">
          <button type="button" class="btn" :class="isOnline === 'hi' ? 'btn-green' : 'btn-default'" @click="isOnline = 'hi'">
            {{ $t('When goes online') }}
          </button>
        </div>
        <div class="btn-group">
          <button type="button" class="btn" :class="isOnline === 'lo' ? 'btn-green' : 'btn-default'" @click="isOnline = 'lo'">
            {{ $t('When goes offline') }}
          </button>
        </div>
      </div>
    </div>

    <ReactionConditionDuration v-model="duration" :default-duration="60" :min-duration="60" />
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
  import ReactionConditionDuration from '@/channels/reactions/params/reaction-condition-duration.vue';
  import {computed} from 'vue';

  const model = defineModel({type: Object});

  if (!model.value) {
    model.value = {on_change_to: {eq: 'lo', name: 'connected', duration_sec: 60}};
  }

  const isOnline = computed({
    get: () => model.value?.on_change_to.eq,
    set: (value) => (model.value = {on_change_to: {...model.value.on_change_to, eq: value}}),
  });

  const duration = computed({
    get: () => model.value?.on_change_to.duration_sec || 60,
    set: (value) => (model.value = {on_change_to: {...model.value.on_change_to, duration_sec: value}}),
  });
</script>

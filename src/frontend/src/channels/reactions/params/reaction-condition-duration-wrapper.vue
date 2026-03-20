<template>
  <div>
    <ReactionConditionDuration v-model="duration" :default-duration="defaultDuration" :min-duration="minDuration" />
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

  const props = defineProps({
    defaultDuration: {type: Number, default: 0},
    minDuration: {type: Number, default: 1},
    field: String,
    eq: String,
  });

  const model = defineModel({type: Object});

  const duration = computed({
    get: () => (Number.isFinite(model.value?.on_change_to?.duration_sec) ? model.value.on_change_to.duration_sec : props.defaultDuration),
    set: (duration) => (model.value = {on_change_to: {eq: props.eq, name: props.field, duration_sec: duration}}),
  });
</script>

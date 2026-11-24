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
    triggerType: {type: String, default: 'on_change_to'},
  });

  const model = defineModel({type: Object});

  const trigger = computed(() => (props.triggerType === 'on_change' ? 'on_change' : 'on_change_to'));

  const duration = computed({
    get: () => (Number.isFinite(model.value?.[trigger.value]?.duration_sec) ? model.value[trigger.value].duration_sec : props.defaultDuration),
    set: (duration) => {
      const definition = {duration_sec: duration};
      if (trigger.value === 'on_change_to') {
        definition.eq = props.eq;
      }
      if (props.field !== undefined) {
        definition.name = props.field;
      }
      model.value = {[trigger.value]: definition};
    },
  });
</script>

<script setup>
  import ChannelTile from '@/channels/channel-tile.vue';
  import ActionableSubjectType from '@/common/enums/actionable-subject-type.js';
  import {useSubject} from '@/stores/subjects-store.js';
  import SceneTile from '@/scenes/scene-tile.vue';
  import ChannelGroupTile from '@/channel-groups/channel-group-tile.vue';
  import ScheduleTile from '@/schedules/schedule-list/schedule-tile.vue';

  const props = defineProps({model: Object});

  const {subject} = useSubject(props.model);
</script>

<template>
  <template v-if="subject">
    <ChannelTile v-if="model.subjectType === ActionableSubjectType.CHANNEL" :model="subject" />
    <ChannelGroupTile v-if="model.subjectType === ActionableSubjectType.CHANNEL_GROUP" :model="subject" />
    <SceneTile v-if="model.subjectType === ActionableSubjectType.SCENE" :model="subject" />
    <ScheduleTile v-if="model.subjectType === ActionableSubjectType.SCHEDULE" :model="subject" />
  </template>
</template>

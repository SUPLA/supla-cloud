import {defineStore, storeToRefs} from 'pinia';
import ActionableSubjectType from '@/common/enums/actionable-subject-type.js';
import {useChannelsStore} from '@/stores/channels-store.js';
import {useScenesStore} from '@/stores/scenes-store.js';
import {useChannelGroupsStore} from '@/stores/channel-groups-store.js';
import {computed, toRef, unref, watch} from 'vue';
import {useSchedulesStore} from '@/stores/schedules-store.js';
import {useNotificationsStore} from '@/stores/notifications-store.js';

export const useSubjectsStore = defineStore('subjects', () => {
  const channelsStore = useChannelsStore();
  const scenesStore = useScenesStore();
  const channelGroupsStore = useChannelGroupsStore();
  const schedulesStore = useSchedulesStore();
  const notificationsStore = useNotificationsStore();

  const {all: channels, ready: channelsReady} = storeToRefs(channelsStore);
  const {all: scenes, ready: scenesReady} = storeToRefs(scenesStore);
  const {all: channelGroups, ready: channelGroupsReady} = storeToRefs(channelGroupsStore);
  const {all: schedules, ready: schedulesReady} = storeToRefs(schedulesStore);
  const {all: notifications, ready: notificationsReady} = storeToRefs(notificationsStore);

  const getByTypeAndId = (type, id) => {
    const key = String(id ?? '');
    if (!key) return null;

    if (type === ActionableSubjectType.CHANNEL) return channels.value[key] || null;
    if (type === ActionableSubjectType.SCENE) return scenes.value[key] || null;
    if (type === ActionableSubjectType.CHANNEL_GROUP) return channelGroups.value[key] || null;
    if (type === ActionableSubjectType.SCHEDULE) return schedules.value[key] || null;
    if (type === ActionableSubjectType.NOTIFICATION) return notifications.value[key] || null;
    return null;
  };

  const groupRefsByType = (refs) => {
    const grouped = {
      [ActionableSubjectType.CHANNEL]: new Set(),
      [ActionableSubjectType.SCENE]: new Set(),
      [ActionableSubjectType.CHANNEL_GROUP]: new Set(),
      [ActionableSubjectType.SCHEDULE]: new Set(),
      [ActionableSubjectType.NOTIFICATION]: new Set(),
    };

    for (const r of refs || []) {
      if (!r) continue;
      const type = r.subjectType;
      const id = String(r.subjectId ?? '');
      if (!id) continue;
      if (grouped[type]) grouped[type].add(id);
    }

    return grouped;
  };

  const ensureSubjectsLoaded = async (refs) => {
    const grouped = groupRefsByType(refs);
    const tasks = [];

    if (!channelsReady.value && grouped[ActionableSubjectType.CHANNEL].size) {
      tasks.push(channelsStore.fetchAll());
    }
    if (!scenesReady.value && grouped[ActionableSubjectType.SCENE].size) {
      tasks.push(scenesStore.fetchAll());
    }
    if (!channelGroupsReady.value && grouped[ActionableSubjectType.CHANNEL_GROUP].size) {
      tasks.push(channelGroupsStore.fetchAll());
    }
    if (!schedulesReady.value && grouped[ActionableSubjectType.SCHEDULE].size) {
      tasks.push(schedulesStore.fetchAll());
    }
    if (!notificationsReady.value && grouped[ActionableSubjectType.NOTIFICATION].size) {
      tasks.push(notificationsStore.fetchAll());
    }
    await Promise.all(tasks);
  };

  const fetchOne = async (subject) => {
    if (subject.ownSubjectType === ActionableSubjectType.CHANNEL) {
      await channelsStore.fetchChannel(subject.id);
    }
  };

  return {getByTypeAndId, ensureSubjectsLoaded, fetchOne};
});

export function useSubject(model) {
  const subjectsStore = useSubjectsStore();

  const modelRef = model && typeof model === 'object' && 'value' in model ? model : toRef({model}, 'model');

  const subject = computed(() => {
    const model = unref(modelRef);
    if (!model) return null;
    return subjectsStore.getByTypeAndId(model.subjectType, model.subjectId);
  });

  watch(
    modelRef,
    (model) => {
      if (!model) return;
      subjectsStore.ensureSubjectsLoaded([model]);
    },
    {immediate: true}
  );

  return {subject};
}

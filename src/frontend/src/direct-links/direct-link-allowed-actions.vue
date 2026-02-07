<script setup>
  import {computed} from 'vue';
  import ChannelFunction from '@/common/enums/channel-function.js';
  import Toggler from '../common/gui/toggler.vue';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store.js';
  import {storeToRefs} from 'pinia';
  import {useSubject} from '@/stores/subjects-store.js';
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import CopyButton from '@/common/copy-button.vue';

  const props = defineProps({directLink: Object});
  const {slugs} = storeToRefs(useDirectLinksStore());
  const {subject} = useSubject(props.directLink);
  const selectedActions = defineModel({default: []});

  const possibleActions = computed(() => {
    if (props.directLink && subject.value) {
      // OPEN and CLOSE actions are not supported for valves via API
      const disableOpenClose = [ChannelFunction.VALVEPERCENTAGE].includes(subject.value.functionId);
      return [
        {
          id: 1000,
          name: 'READ',
          caption: 'Read',
          nameSlug: 'read',
        },
      ]
        .concat(subject.value.possibleActions)
        .filter((action) => !disableOpenClose || (action.name !== 'OPEN' && action.name !== 'CLOSE'));
    }
    return [];
  });

  const {config} = storeToRefs(useFrontendConfigStore());

  const urlBeginning = computed(() => {
    const slug = slugs.value[props.directLink.id] ?? 'TOKEN';
    return `${config.value.suplaUrl}/direct/${props.directLink.id}/${slug}`;
  });
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<template>
  <div v-for="action in possibleActions" :key="action.id" class="direct-link-action-row">
    <div class="action-column action-toggler">
      <toggler v-model="selectedActions" :value="action.name" />
    </div>
    <div class="action-column action-caption">
      {{ $t(action.caption) }}
    </div>
    <div class="action-column action-url">{{ urlBeginning }}/{{ action.nameSlug }}</div>
    <div class="action-column action-copy">
      <copy-button :text="urlBeginning"></copy-button>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .direct-link-action-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;

    .action-column {
      &.action-toggler {
        flex: 0 0 auto;
      }

      &.action-caption {
        flex: 0 0 120px;
        font-weight: 500;
      }

      &.action-url {
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      &.action-copy {
        flex: 0 0 auto;
      }
    }

    @media (max-width: 768px) {
      flex-direction: column;
      align-items: stretch;
      gap: 0.5rem;

      .action-column {
        &.action-caption,
        &.action-url {
          flex: 1 1 auto;
        }

        &.action-url {
          word-break: break-all;
          white-space: normal;
        }
      }
    }
  }
</style>

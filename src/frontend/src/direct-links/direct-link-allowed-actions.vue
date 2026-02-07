<script setup>
  import {computed, ref} from 'vue';
  import ChannelFunction from '@/common/enums/channel-function.js';
  import Toggler from '../common/gui/toggler.vue';
  import {useSubject} from '@/stores/subjects-store.js';
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';
  import DirectLinkRequestExample from '@/direct-links/direct-link-request-example.vue';
  import DirectLinkRequestCodeExample from '@/direct-links/direct-link-request-code-example.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {useDirectLinkExamples} from '@/direct-links/direct-link-examples.js';

  const props = defineProps({directLink: Object});
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

  const exampleMode = ref('link');

  const {exampleModeLabels} = useDirectLinkExamples();
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<template>
  <div class="text-right mb-3">
    <DropdownMenu class="d-inline-block">
      <DropdownMenuTrigger class="btn btn-default btn-wrapped"> {{ $t('Show examples') }}: {{ $t(exampleModeLabels[exampleMode]) }} </DropdownMenuTrigger>
      <ul class="dropdown-menu">
        <li v-for="mode in Object.keys(exampleModeLabels)" :key="mode" :class="{active: mode === exampleMode}">
          <a @click="exampleMode = mode">
            {{ $t(exampleModeLabels[mode]) }}
          </a>
        </li>
      </ul>
    </DropdownMenu>
  </div>
  <TransitionExpand>
    <DirectLinkRequestCodeExample v-if="exampleMode !== 'link'" :mode="exampleMode" :direct-link="directLink" />
  </TransitionExpand>
  <div v-for="action in possibleActions" :key="action.id" class="direct-link-action-row py-3">
    <div class="action-column action-toggler">
      <toggler v-model="selectedActions" :value="action.name" />
    </div>
    <div class="action-column action-caption">
      {{ $t(action.caption) }}
    </div>
    <div class="action-column action-url">
      <DirectLinkRequestExample :action="action" :mode="exampleMode" :direct-link="directLink" />
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

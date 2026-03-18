<script setup>
  import {computed, inject, watch} from 'vue';
  import {faChevronRight} from '@fortawesome/free-solid-svg-icons';
  import TransitionExpand from '@/common/gui/transition-expand.vue';

  const props = defineProps({
    titleI18n: String,
    name: {
      type: String,
      default: () => self.crypto.randomUUID(),
    },
    iconOpened: {
      type: Object,
    },
  });
  const model = defineModel({type: Boolean});

  const accordion = inject('accordion');
  if (!accordion) {
    throw new Error("AccordionItem must be used inside AccordionRoot (missing provide('accordion')).");
  }

  const isOpen = computed(() => accordion.openItems.value.includes(props.name));

  const setOpen = (open) => {
    if (open === isOpen.value) {
      return;
    }
    accordion.toggleItem(props.name);
  };

  const toggle = () => accordion.toggleItem(props.name);

  watch(
    isOpen,
    (v) => {
      if (model.value !== v) {
        model.value = v;
      }
    },
    {immediate: true}
  );

  watch(
    model,
    (v) => {
      const next = !!v;
      if (next !== isOpen.value) {
        setOpen(next);
      }
    },
    {immediate: true}
  );
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<template>
  <div class="accordion-item" :class="{open: isOpen}">
    <a class="d-flex align-items-flex-start accordion-header" @click="toggle">
      <span class="flex-grow-1">{{ $t(titleI18n) }}</span>
      <span :class="{'accordion-header-icon': !iconOpened}">
        <fa :icon="(isOpen && iconOpened) || faChevronRight" />
      </span>
    </a>
    <TransitionExpand>
      <div v-show="isOpen" class="mb-3">
        <slot />
      </div>
    </TransitionExpand>
  </div>
</template>

<style scoped lang="scss">
  .accordion-item {
    .accordion-header {
      color: inherit;
      font-size: 1.1em;
      padding: 0.5em 0;
      margin: 0;
      border-top: 1px solid #ccc;
    }
    &:first-child .accordion-header {
      border-top: none;
    }
    .accordion-header-icon {
      transition: transform 0.2s;
    }
    &.open .accordion-header-icon {
      transform: rotate(90deg);
    }
  }
</style>

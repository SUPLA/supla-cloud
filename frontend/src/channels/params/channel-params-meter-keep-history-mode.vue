<template>
  <div>
    <dl>
      <dd>{{ $t(labelI18n) }}</dd>
      <dt class="text-center">
        <toggler v-model="keepHistory" />
      </dt>
    </dl>
    <transition-expand>
      <div v-if="!initialKeepHistory && keepHistory" class="alert alert-warning mt-3">
        {{ $t('keepHistoryMeasurementWarningSelected') }}
      </div>
    </transition-expand>
  </div>
</template>

<script>
  import TransitionExpand from '../../common/gui/transition-expand.vue';
  import EventBus from '../../common/event-bus';
  import Toggler from '@/common/gui/toggler.vue';

  export default {
    components: {Toggler, TransitionExpand},
    props: {
      value: Boolean,
      labelI18n: {
        type: String,
        default: 'Store measurements history', // i18n
      },
    },
    data() {
      return {
        initialKeepHistory: undefined,
        channelSavedListener: undefined,
      };
    },
    computed: {
      keepHistory: {
        get() {
          return !!this.value;
        },
        set(value) {
          this.$emit('input', !!value);
        },
      },
    },
    mounted() {
      this.channelSavedListener = () => this.updateInitialKeepHistory();
      EventBus.$on('channel-updated', this.channelSavedListener);
      this.updateInitialKeepHistory();
    },
    beforeUnmount() {
      EventBus.$off('channel-updated', this.channelSavedListener);
    },
    methods: {
      updateInitialKeepHistory() {
        this.initialKeepHistory = !!this.value;
      },
    },
  };
</script>

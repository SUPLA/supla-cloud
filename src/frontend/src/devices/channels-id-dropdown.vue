<template>
  <channels-dropdown
    v-model="channel"
    :params="params"
    :hide-none="hideNone"
    :choose-prompt-i18n="choosePromptI18n"
    :disabled="disabled"
    :filter="filter"
    @input="channelChanged()"
  ></channels-dropdown>
</template>

<script>
  import ChannelsDropdown from './channels-dropdown.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {ChannelsDropdown},
    props: ['value', 'params', 'hideNone', 'choosePromptI18n', 'disabled', 'filter'],
    data() {
      return {
        channel: undefined,
      };
    },
    watch: {
      value() {
        this.updateChannel();
      },
    },
    mounted() {
      this.updateChannel();
    },
    methods: {
      updateChannel() {
        if (this.value) {
          api.get(`channels/${this.value}`).then((response) => {
            this.channel = response.body;
            this.emitChannel();
          });
        } else {
          this.channel = undefined;
          this.emitChannel();
        }
      },
      channelChanged() {
        this.$nextTick(() => {
          this.$emit('input', this.channel?.id || 0);
          this.emitChannel();
        });
      },
      emitChannel() {
        this.$emit('channelChanged', this.channel);
      },
    },
  };
</script>

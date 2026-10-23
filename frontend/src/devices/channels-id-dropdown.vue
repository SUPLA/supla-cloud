<template>
  <channels-dropdown
    v-model="channel"
    :params="params"
    :hide-none="hideNone"
    :choose-prompt-i18n="choosePromptI18n"
    :disabled="disabled"
    :filter="filter"
    :destination="destination"
    :reference-role="referenceRole"
    @input="channelChanged()"
  ></channels-dropdown>
</template>

<script>
  import ChannelsDropdown from './channels-dropdown.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {ChannelsDropdown},
    props: ['value', 'params', 'hideNone', 'choosePromptI18n', 'disabled', 'filter', 'destination', 'referenceRole'],
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
          const requestedId = this.value;
          // Keep identity while loading or after a 404. Only explicit input changes the config.
          this.channel = {id: requestedId};
          api
            .get(`channels/${requestedId}`)
            .then((response) => {
              if (this.value === requestedId) {
                this.channel = response.body;
                this.emitChannel();
              }
            })
            .catch(() => {
              if (this.value === requestedId) {
                this.channel = {id: requestedId};
              }
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

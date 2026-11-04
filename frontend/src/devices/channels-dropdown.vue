<template>
  <div>
    <SelectForSubjects
      v-model="chosenChannel"
      class="channel-dropdown"
      :none-option="!hideNone"
      :options="channelsForDropdown"
      :option-groups="
        referenceRole
          ? [
              {id: 'local', labelI18n: 'Channels on this device'},
              {id: 'remote', labelI18n: 'Channels on other devices'},
            ]
          : []
      "
      :caption="channelCaption"
      :search-text="channelSearchText"
      :option-html="channelHtml"
      :choose-prompt-i18n="choosePromptI18n || 'choose the channel'"
      :disabled="disabled"
    />
    <p v-if="staleSelection" class="text-warning small" role="alert">
      {{ $t('Selected channel is unavailable or incompatible. Choose another channel or clear the selection.') }} (ID{{ value.id }})
    </p>
    <button v-if="staleSelection && !disabled" type="button" class="btn btn-default btn-sm" @click="$emit('input', undefined)">
      {{ $t('Clear selection') }}
    </button>
  </div>
</template>

<script>
  import {localFirstCandidates} from '@/channels/channel-reference-candidates';
  import {channelIconUrl} from '@/common/filters';
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {useSubDevicesStore} from '@/stores/subdevices-store';
  import {mapState, mapStores} from 'pinia';
  import {useDevicesStore} from '@/stores/devices-store';
  import {useLocationsStore} from '@/stores/locations-store';
  import {useChannelsStore} from '@/stores/channels-store';

  export default {
    components: {SelectForSubjects},
    props: ['params', 'value', 'hiddenChannels', 'hideNone', 'filter', 'choosePromptI18n', 'disabled', 'destination', 'referenceRole'],
    mounted() {
      this.subDevicesStore.fetchAll();
    },
    methods: {
      channelCaption(channel) {
        const caption = channel.caption || `ID${channel.id} ${this.$t(channel.function?.caption || '')}`;
        return this.referenceRole && channel.iodeviceId !== this.destination.iodeviceId
          ? `${caption} / ${this.devices[channel.iodeviceId]?.name || `ID${channel.iodeviceId}`}`
          : caption;
      },
      channelSearchText(channel) {
        const subDevice = this.subDevicesStore.forChannel(channel);
        const device = this.devices[channel.iodeviceId] || {};
        const location = this.locations[channel.locationId] || {};
        return `${channel.caption || ''} ID${channel.id} ${this.$t(channel.function.caption)} ${location.caption} ${device.name} ${subDevice?.name || ''}`;
      },
      channelHtml(channel, escape) {
        const subDevice = this.subDevicesStore.forChannel(channel);
        const subDeviceName = subDevice ? ' / ' + escape(subDevice.name) : '';
        const device = this.devices[channel.iodeviceId] || {};
        const location = this.locations[channel.locationId] || {};
        return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(channel.fullCaption)}</span>
                                        ${channel.caption ? `<span class="small text-muted">ID${channel.id} ${this.$t(channel.function.caption)}</span>` : ''}
                                    </h5>
                                    <p class="line-clamp line-clamp-2 small mb-0 option-extra">${escape(location.caption)} / ${escape(device.name)}${subDeviceName}</p>
                                </div>
                                <div class="icon option-extra"><img src="${channelIconUrl(channel)}"></div>
                            </div>
                        </div>`;
      },
    },
    computed: {
      staleSelection() {
        return !!(this.referenceRole && this.value?.id && !this.channelsForDropdown.some((candidate) => candidate.id === this.value.id));
      },
      channelsForDropdown() {
        if (!this.channels) {
          return [];
        }
        let filter = this.filter || (() => true);
        if (this.hiddenChannels && this.hiddenChannels.length) {
          const filterOriginal = filter;
          const hiddenIds = this.hiddenChannels.map((channel) => channel.id || channel);
          filter = (channel) => !hiddenIds.includes(channel.id) && filterOriginal(channel);
        }
        const filtered = this.channels.filter(filter);
        const channels = this.referenceRole ? localFirstCandidates(this.destination, filtered, this.devices, this.referenceRole) : filtered;
        this.$emit('update', channels);
        return channels.map((channel) => {
          return {...channel, fullCaption: this.channelCaption(channel)};
        });
      },
      chosenChannel: {
        get() {
          return this.value;
        },
        set(channel) {
          this.$emit('input', channel);
        },
      },
      ...mapState(useDevicesStore, {devices: 'all'}),
      ...mapState(useLocationsStore, {locations: 'all'}),
      ...mapState(useChannelsStore, {
        channels(store) {
          return store.filteredChannels(this.params);
        },
      }),
      ...mapStores(useSubDevicesStore),
    },
  };
</script>

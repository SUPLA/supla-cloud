<template>
    <div>
        <SelectForSubjects
            class="channel-dropdown"
            :none-option="!hideNone"
            :options="channelsForDropdown"
            :caption="channelCaption"
            :option-html="channelHtml"
            :choose-prompt-i18n="choosePromptI18n || 'choose the channel'"
            :disabled="disabled"
            v-model="chosenChannel"/>
    </div>
</template>

<script>
    import {channelIconUrl} from "../common/filters";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";
    import {useSubDevicesStore} from "@/stores/subdevices-store";
    import {mapStores} from "pinia";

    export default {
        props: ['params', 'value', 'hiddenChannels', 'hideNone', 'filter', 'choosePromptI18n', 'disabled'],
        components: {SelectForSubjects},
        data() {
            return {
                channels: undefined,
            };
        },
        mounted() {
            this.subDevicesStore.fetchAll();
            this.fetchChannels();
        },
        methods: {
            fetchChannels() {
                this.channels = undefined;
                return this.$http.get('channels?include=iodevice,location&' + (this.params || '')).then(({body: channels}) => {
                    this.channels = channels;
                });
            },
            channelCaption(channel) {
                return channel.caption || `ID${channel.id} ${this.$t(channel.function.caption)}`;
            },
            channelHtml(channel, escape) {
                const subDevice = this.subDevicesStore.forChannel(channel);
                const subDeviceName = subDevice ? ' / ' + escape(subDevice.name) : '';
                return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(channel.fullCaption)}</span>
                                        ${channel.caption ? `<span class="small text-muted">ID${channel.id} ${this.$t(channel.function.caption)}</span>` : ''}
                                    </h5>
                                    <p class="line-clamp line-clamp-2 small mb-0 option-extra">${escape(channel.location.caption)} / ${escape(channel.iodevice.name)}${subDeviceName}</p>
                                </div>
                                <div class="icon option-extra"><img src="${channelIconUrl(channel)}"></div></div>
                            </div>
                        </div>`;
            },
        },
        computed: {
            channelsForDropdown() {
                if (!this.channels) {
                    return [];
                }
                let filter = this.filter || (() => true);
                if (this.hiddenChannels && this.hiddenChannels.length) {
                    const filterOriginal = filter;
                    const hiddenIds = this.hiddenChannels.map(channel => channel.id || channel);
                    filter = ((channel) => !hiddenIds.includes(channel.id) && filterOriginal(channel));
                }
                const channels = this.channels.filter(filter);
                this.$emit('update', channels);
                return channels.map(channel => {
                    channel.fullCaption = this.channelCaption(channel);
                    return channel;
                })
            },
            chosenChannel: {
                get() {
                    return this.value;
                },
                set(channel) {
                    this.$emit('input', channel);
                }
            },
            ...mapStores(useSubDevicesStore),
        },
        watch: {
            params() {
                this.fetchChannels();
            },
        }
    };
</script>

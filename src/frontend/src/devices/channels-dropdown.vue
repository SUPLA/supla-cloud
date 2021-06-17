<template>
    <div>
        <div class="select-loader"
            v-if="!channels">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="selectpicker"
            :disabled="!channels"
            ref="dropdown"
            data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            data-width="100%"
            :data-none-selected-text="$t('choose the channel')"
            :data-none-results-text="$t('No results match {0}')"
            v-model="chosenChannel"
            @change="$emit('input', chosenChannel)">
            <option :value="undefined"
                :title="$t('choose the channel')"
                v-show="!hideNone && chosenChannel">{{ $t('None') }}
            </option>
            <option v-for="channel in channelsForDropdown"
                :key="channel.id"
                :value="channel"
                :data-content="channelHtml(channel)">
                {{ channelCaption(channel) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import $ from "jquery";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelIconUrl} from "../common/filters";

    export default {
        props: ['params', 'value', 'hiddenChannels', 'hideNone', 'filter'],
        components: {ButtonLoadingDots},
        data() {
            return {
                channels: undefined,
                chosenChannel: undefined
            };
        },
        mounted() {
            this.fetchChannels();
        },
        methods: {
            fetchChannels() {
                this.channels = undefined;
                this.$http.get('channels?include=iodevice,location&' + (this.params || '')).then(({body: channels}) => {
                    this.channels = channels;
                    this.setChannelFromModel();
                    Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
                });
            },
            channelCaption(channel) {
                return channel.caption || `ID${channel.id} ${this.$t(channel.function.caption)}`;
            },
            channelHtml(channel) {
                let content = `<div class='subject-dropdown-option flex-left-full-width'>`
                    + `<div class="labels full"><h4>${this.channelCaption(channel)}`;
                if (channel.caption) {
                    content += ` <span class='small text-muted'>ID${channel.id} ${this.$t(channel.function.caption)}</span>`;
                }
                content += '</h4>';
                content += `<p>${channel.location.caption} / ${channel.iodevice.name}</p></div>`;
                content += `<div class="icon"><img src="${channelIconUrl(channel)}"></div></div>`;
                return content;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setChannelFromModel() {
                if (this.value && this.channels) {
                    this.chosenChannel = this.channels.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenChannel = undefined;
                }
            }
        },
        computed: {
            channelsForDropdown() {
                this.updateDropdownOptions();
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
                this.updateDropdownOptions();
                return channels;
            }
        },
        watch: {
            value() {
                this.setChannelFromModel();
                this.updateDropdownOptions();
            },
            params() {
                this.fetchChannels();
            }
        }
    };
</script>

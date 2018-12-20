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
            <option value="0"
                v-if="!hideNone && chosenChannel">{{ $t('None') }}
            </option>
            <option v-for="channel in channelsForDropdown"
                :value="channel"
                :data-content="channelHtml(channel)"
                :title="channelTitle(channel)">
                {{ channelTitle(channel) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelIconUrl, channelTitle} from "../common/filters";

    export default {
        props: ['params', 'value', 'hiddenChannels', 'hideNone', 'initialId', 'filter'],
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
                    this.channels = channels.filter(this.filter || (() => true));
                    if (this.initialId) {
                        this.chosenChannel = this.channels.filter(ch => ch.id == this.initialId)[0];
                    }
                    this.setChannelFromModel();
                    Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
                });
            },
            channelTitle(channel) {
                return channelTitle(channel, this, true);
            },
            channelHtml(channel) {
                let content = `<div class='channel-dropdown-option flex-left-full-width'>`
                    + `<div class="labels"><h4>ID${channel.id} ${this.$t(channel.function.caption)}`;
                if (channel.caption) {
                    content += ` <span class='small text-muted'>${channel.caption}</span>`;
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
                let channels = [];
                if (this.hiddenChannels && this.hiddenChannels.length) {
                    const hiddenIds = this.hiddenChannels.map(channel => channel.id || channel);
                    channels = this.channels.filter(channel => hiddenIds.indexOf(channel.id) < 0);
                } else {
                    channels = this.channels;
                }
                this.$emit('update', channels);
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

<style lang="scss">
    @import "../styles/variables";

    .select-loader {
        position: relative;
        text-align: center;
        .button-loading-dots {
            position: absolute;
            top: 8px;
            left: 50%;
            margin-left: -25px;
            z-index: 20;
        }
    }

    .channel-dropdown-option {
        padding: 5px 3px;
        .icon {
            img {
                height: 60px;
            }
        }
    }
</style>

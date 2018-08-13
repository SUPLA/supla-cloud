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
            v-model="chosenChannelId">
            <option value="0"
                v-if="!hideNone">{{ $t('None') }}
            </option>
            <optgroup :label="$t('Channels')">
                <option v-for="channel in channelsForDropdown"
                    :value="channel.id"
                    :data-content="channelHtml(channel)"
                    :title="channelTitle(channel)">
                    {{ channelTitle(channel) }}
                </option>
            </optgroup>
            <optgroup :label="$t('Channel groups')">
                <option v-for="channel in channelsForDropdown"
                    :value="channel.id"
                    :data-content="channelHtml(channel)"
                    :title="channelTitle(channel)">
                    {{ channelTitle(channel) }}
                </option>
            </optgroup>
        </select>
    </div>
</template>

<style lang="scss">
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
</style>

<script>
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelTitle} from "../common/filters";

    export default {
        props: ['params', 'value', 'hiddenChannels', 'hideNone'],
        components: {ButtonLoadingDots},
        data() {
            return {
                channels: undefined,
                chosenChannelId: undefined
            };
        },
        mounted() {
            this.fetchChannels();
        },
        methods: {
            fetchChannels() {
                // $(this.$refs.dropdown).chosen("destroy");
                this.channels = undefined;
                this.$http.get('channels?' + this.params).then(({body: channels}) => {
                    this.channels = channels;
                    Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
                    // .sele().change((e) => {
                    //         this.chosenChannelId = e.currentTarget.value;
                    //         this.channelChanged();
                    //     }));
                });
            },
            channelTitle(channel) {
                return channelTitle(channel, this, true);
            },
            channelHtml(channel) {
                let content = `<div class='channel-dropdown-option'>`
                    + `<div class="labels"><h4>ID${channel.id} ${ this.$t(channel.function.caption) }`;
                if (channel.caption) {
                    content += ` <span class='small text-muted'>${channel.caption}</span>`;
                }
                content += '</h4>';
                content += `<p>${channel.location.caption} / ${channel.iodevice.name}</p></div>`;
                content += `<div class="icon"><img src='assets/img/functions/${channel.function.id}.svg'></div></div>`;
                return content;
            },
            channelChanged() {
                if (this.chosenChannelId) {
                    const channel = this.channels.find(c => c.id == this.chosenChannelId);
                    this.$emit('input', channel);
                } else {
                    this.$emit('input', undefined);
                }
            },
            updateDropdownOptions() {
                // Vue.nextTick(() => $(this.$refs.dropdown).trigger("chosen:updated"));
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
                this.chosenChannelId = this.value ? this.value.id : undefined;
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

    .bootstrap-select {
        .dropdown-menu > li {
            > a {
                white-space: normal;
            }
            &.active > a {
                &, &:focus, &:hover {
                    background-color: $supla-green;
                }
            }
        }
        .text {
            width: 100%;
            .channel-dropdown-option {
                display: flex;
                .labels {
                    flex: 1;
                }
                .icon {
                    img { height: 60px; }
                }
            }
        }
    }
</style>

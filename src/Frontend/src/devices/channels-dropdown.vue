<template>
    <div>
        <div class="select-loader"
            v-if="!channels">
            <button-loading-dots></button-loading-dots>
        </div>
        <select class="form-control"
            :disabled="!channels"
            ref="dropdown"
            :data-placeholder="$t('choose the channel')"
            v-model="chosenChannelId">
            <option value="0">{{ $t('None') }}</option>
            <option v-for="channel in channelsForDropdown"
                :value="channel.id">
                {{ channelTitle(channel) }}
            </option>
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
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {channelTitle} from "../common/filters";

    export default {
        props: ['params', 'value', 'hiddenChannels'],
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
                $(this.$refs.dropdown).chosen("destroy");
                this.channels = undefined;
                this.$http.get('channels?' + this.params).then(({body: channels}) => {
                    this.channels = channels;
                    Vue.nextTick(() => $(this.$refs.dropdown).chosen().change((e) => {
                        this.chosenChannelId = e.currentTarget.value;
                        this.channelChanged();
                    }));
                });
            },
            channelTitle(channel) {
                return channelTitle(channel, this, true);
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
                Vue.nextTick(() => $(this.$refs.dropdown).trigger("chosen:updated"));
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

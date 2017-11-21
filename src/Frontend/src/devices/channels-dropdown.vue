<template>
    <select class="form-control"
        ref="dropdown"
        :data-placeholder="$t('choose the channel')"
        v-model="chosenChannelId">
        <option v-for="channel in channelsForDropdown"
            :value="channel.id">
            {{ channelTitle(channel) }}
        </option>
    </select>
</template>

<script>
    import Vue from "vue";
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";

    export default {
        props: ['params', 'value', 'hiddenChannels'],
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
                return (channel.caption || this.$t(channel.function.caption)) + ` (${channel.iodevice.location.caption} / ${channel.iodevice.name})`;
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
                if (this.hiddenChannels && this.hiddenChannels.length) {
                    const hiddenIds = this.hiddenChannels.map(channel => channel.id || channel);
                    return this.channels.filter(channel => hiddenIds.indexOf(channel.id) < 0);
                } else {
                    return this.channels;
                }
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

<template>
    <dl>
        <dd>{{ $t(labelI18n) }}</dd>
        <dt class="text-center"
            style="font-weight: normal">
            <channels-dropdown :params="this.channelFilter"
                v-model="relatedChannel"
                @input="relatedChannelChanged()"></channels-dropdown>
        </dt>
    </dl>
</template>

<script>
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['channel', 'labelI18n', 'paramNo', 'channelFilter'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
        mounted() {
            this.updateSecondaryChannel();
        },
        methods: {
            updateSecondaryChannel() {
                if (this.paramValue) {
                    this.$http.get(`channels/${this.paramValue}`).then(response => this.relatedChannel = response.body);
                } else {
                    this.relatedChannel = undefined;
                }
            },
            relatedChannelChanged() {
                this.paramValue = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        },
        computed: {
            paramNumber() {
                return +(this.paramNo || 1);
            },
            paramField() {
                return `param${this.paramNumber}`;
            },
            paramValue: {
                get() {
                    return +(this.channel[this.paramField]);
                }, set(value) {
                    this.channel[this.paramField] = value;
                }
            }
        },
        watch: {
            'channel.param1'() {
                if (this.paramNumber === 1) {
                    this.updateSecondaryChannel();
                }
            },
            'channel.param2'() {
                if (this.paramNumber === 2) {
                    this.updateSecondaryChannel();
                }
            },
            'channel.param3'() {
                if (this.paramNumber === 3) {
                    this.updateSecondaryChannel();
                }
            },
            'channel.param4'() {
                if (this.paramNumber === 4) {
                    this.updateSecondaryChannel();
                }
            },
        }
    };
</script>

<template>
    <div>
        <channel-params-controlling-any-lock :channel="channel"
            @change="$emit('change')"
            :times="[500, 1000, 2000]"
            related-channel-function="OPENINGSENSOR_GATE"></channel-params-controlling-any-lock>
        <dl>
            <dd>{{ $t('Partial opening sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown params="include=iodevice,location,function&function=OPENINGSENSOR_GATE"
                    v-model="secondarySensor"
                    @input="secondarySensorChanged()"></channels-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown, ChannelParamsControllingAnyLock},
        props: ['channel'],
        data() {
            return {
                secondarySensor: undefined,
            };
        },
        mounted() {
            this.updateSecondarySensor();
        },
        methods: {
            updateSecondarySensor() {
                if (this.channel.param3) {
                    this.$http.get(`channels/${this.channel.param3}`).then(response => this.secondarySensor = response.body);
                } else {
                    this.secondarySensor = undefined;
                }
            },
            secondarySensorChanged() {
                this.channel.param3 = this.secondarySensor ? this.secondarySensor.id : 0;
                this.$emit('change');
            }
        },
        watch: {
            'channel.param2'() {
                if (this.channel.param2 == this.channel.param3) {
                    this.channel.param3 = 0;
                }
            },
            'channel.param3'() {
                if (this.channel.param2 == this.channel.param3) {
                    this.channel.param2 = 0;
                }
                this.updateSecondarySensor();
            }
        }
    };
</script>

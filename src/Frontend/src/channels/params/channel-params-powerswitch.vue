<template>
    <div>
        <dl>
            <dd>{{ $t('Associated measurement channel') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown params="function=ELECTRICITYMETER"
                    v-model="secondaryChannel"
                    @input="secondaryChannelChanged()"></channels-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['channel'],
        data() {
            return {
                secondaryChannel: undefined,
            };
        },
        mounted() {
            this.updateSecondaryChannel();
        },
        methods: {
            updateSecondaryChannel() {
                if (this.channel.param1) {
                    this.$http.get(`channels/${this.channel.param1}`).then(response => this.secondaryChannel = response.body);
                } else {
                    this.secondaryChannel = undefined;
                }
            },
            secondaryChannelChanged() {
                this.channel.param1 = this.secondaryChannel ? this.secondaryChannel.id : 0;
                this.$emit('change');
            }
        },
        watch: {
            'channel.param1'() {
                this.updateSecondaryChannel();
            }
        }
    };
</script>

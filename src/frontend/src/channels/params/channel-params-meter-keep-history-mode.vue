<template>
    <div>
        <dl>
            <dd>{{ $t(labelI18n) }}</dd>
            <dt class="text-center">
                <toggler v-model="keepHistory"/>
            </dt>
        </dl>
        <transition-expand>
            <div class="alert alert-warning mt-3"
                v-if="!initialKeepHistory && keepHistory">
                {{ $t('keepHistoryMeasurementWarningSelected') }}
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import EventBus from "../../common/event-bus";

    export default {
        components: {TransitionExpand},
        props: {
            value: Boolean,
            labelI18n: {
                type: String,
                default: 'Store measurements history', // i18n
            },
        },
        data() {
            return {
                initialKeepHistory: undefined,
                channelSavedListener: undefined,
            };
        },
        mounted() {
            this.channelSavedListener = () => this.updateInitialKeepHistory();
            EventBus.$on('channel-updated', this.channelSavedListener);
            this.updateInitialKeepHistory();
        },
        methods: {
            updateInitialKeepHistory() {
                this.initialKeepHistory = !!this.value;
            },
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelSavedListener);
        },
        computed: {
            keepHistory: {
                get() {
                    return !!this.value;
                },
                set(value) {
                    this.$emit('input', !!value);
                }
            }
        }
    };
</script>

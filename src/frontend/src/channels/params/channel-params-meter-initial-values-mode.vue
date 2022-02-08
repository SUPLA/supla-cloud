<template>
    <div>
        <label :class="['checkbox2']">
            <input type="checkbox"
                v-model="addToHistory"
                @change="$emit('input', addToHistory)">
            <span>{{ $t('Include energy corrections in new measurements history (not recommended)') }}</span>
        </label>
        <transition-expand>
            <div class="alert alert-danger mt-3"
                v-if="initialAddToHistory !== addToHistory">
                <span v-if="addToHistory">{{ $t('addToHistoryMeasurementWarningSelected') }}</span>
                <span v-else>{{ $t('addToHistoryMeasurementWarningUnselected') }}</span>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import EventBus from "../../common/event-bus";

    export default {
        components: {TransitionExpand},
        props: ['value'],
        data() {
            return {
                addToHistory: undefined,
                initialAddToHistory: undefined,
                channelSavedListener: undefined,
            };
        },
        mounted() {
            this.channelSavedListener = () => this.updateInitialAddToHistory();
            EventBus.$on('channel-updated', this.channelSavedListener);
            this.updateInitialAddToHistory();
            this.addToHistory = this.initialAddToHistory;
        },
        methods: {
            updateInitialAddToHistory() {
                this.initialAddToHistory = !!this.value;
            },
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelSavedListener);
        }
    };
</script>

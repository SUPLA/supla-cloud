<template>
    <div class="form-group">
        <vue-timepicker v-model="time"
            hide-clear-button
            auto-scroll
            advanced-keyboard
            input-width="100%"
            @change="updateTimeExpression()"></vue-timepicker>
    </div>
</template>

<script>
    import VueTimepicker from 'vue2-timepicker/src/vue-timepicker.vue';

    export default {
        props: ['weekdays', 'value'],
        components: {VueTimepicker},
        data() {
            return {
                time: '00:00',
            };
        },
        mounted() {
            if (this.value) {
                const zeroPad = (num, places = 2) => String(num).padStart(places, '0')
                const parts = this.value.split(' ');
                this.time = `${zeroPad(parts[1])}:${zeroPad(parts[0])}`;
            } else {
                this.updateTimeExpression();
            }
        },
        methods: {
            updateTimeExpression() {
                if (this.time) {
                    const timeParts = this.time.split(':');
                    const cronExpression = [+timeParts[1], +timeParts[0], '*', '*', this.weekdays].join(' ');
                    this.$emit('input', cronExpression);
                } else {
                    this.$emit('input', undefined);
                }
            },
        },
        watch: {
            weekdays() {
                this.updateTimeExpression();
            }
        }
    };
</script>

<style lang="scss">
    @import '../../../styles/variables';

    .time-picker {
        .display-time {
            border-radius: 5px;
            text-align: center;
            font-size: 1.3em !important;
        }
        .select-list {
            ul li {
                &.active {
                    background: $supla-green !important;
                }
            }
        }
    }
</style>

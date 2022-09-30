<template>
    <div class="form-group">
        <input type="time"
            class="form-control"
            v-model="theTime">
    </div>
</template>

<script>
    export default {
        props: ['weekdays', 'value'],
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
            zeroPad(num, places = 2) {
                return String(num).padStart(places, '0');
            },
        },
        computed: {
            theTime: {
                set(value) {
                    const timeParts = value.split(':');
                    const cronExpression = [+timeParts[1], +timeParts[0], '*', '*', this.weekdays].join(' ');
                    this.$emit('input', cronExpression);
                },
                get() {
                    if (this.value) {
                        const parts = this.value.split(' ');
                        return `${this.zeroPad(parts[1])}:${this.zeroPad(parts[0])}`;
                    } else {
                        return '00:00';
                    }
                }
            },
        },
        mounted() {
            // this forces the cron expression to be calculated when the component starts
            // eslint-disable-next-line no-self-assign
            this.theTime = this.theTime;
        },
        watch: {
            weekdays() {
                // this forces the cron expression to be recalculated
                // eslint-disable-next-line no-self-assign
                this.theTime = this.theTime;
            }
        }
    };
</script>

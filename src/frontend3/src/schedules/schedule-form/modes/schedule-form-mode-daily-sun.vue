<template>
    <SunriseSunsetOffsetSelector v-model="sunMode"/>
</template>

<script>
    import SunriseSunsetOffsetSelector from "@/schedules/schedule-form/modes/sunrise-sunset-offset-selector.vue";

    export default {
        components: {SunriseSunsetOffsetSelector},
        props: ['weekdays', 'value'],
        watch: {
            weekdays() {
                this.sunMode = {mode: 'sunset', offset: 0, ...this.sunMode};
            }
        },
        mounted() {
            this.sunMode = {mode: 'sunset', offset: 0, ...this.sunMode};
        },
        computed: {
            sunMode: {
                get() {
                    const current = this.value && this.value.match(/^S([SR])(-?[0-9]+)/);
                    if (current) {
                        return {
                            mode: current[1] === 'R' ? 'sunrise' : 'sunset',
                            offset: parseInt(current[2]),
                        }
                    } else {
                        return {};
                    }
                },
                set({mode, offset}) {
                    const sunTimeEncoded = 'S' + (mode === 'sunrise' ? 'R' : 'S') + (offset || 0);
                    const sunExpression = [sunTimeEncoded, 0, '*', '*', this.weekdays].join(' ');
                    this.$emit('input', sunExpression);
                }
            }
        }
    };
</script>

<template>
    <div>
        <dl>
            <dd>{{ $t('Number of sections') }}</dd>
            <dt>
                <input type="number"
                    step="1"
                    min="1"
                    max="7"
                    class="form-control text-center"
                    v-model="channel.config.sectionsCount"
                    @input="$emit('change')">
            </dt>
            <dd>{{ $t('Regeneration starts at') }}</dd>
            <dt class="digiglass-rest-timepicker">
                <input type="time"
                    class="form-control text-center"
                    v-model="regenerationTime">
            </dt>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['channel'],
        data() {
            return {
                picker: undefined,
            };
        },
        computed: {
            regenerationTime: {
                set(value) {
                    const parts = value.split(':');
                    const minuteInDay = +parts[0] * 60 + (+parts[1]);
                    if (minuteInDay !== this.channel.config.regenerationTimeStart) {
                        this.channel.config.regenerationTimeStart = minuteInDay;
                        this.$emit('change');
                    }
                },
                get() {
                    const hour = Math.floor(this.channel.config.regenerationTimeStart / 60);
                    const minutes = this.channel.config.regenerationTimeStart % 60;
                    return `${hour}:${minutes}`;
                }
            },
        },
    };
</script>

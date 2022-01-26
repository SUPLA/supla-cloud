<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-white"
            type="button"
            v-for="mode in availableModes"
            :key="mode"
            @click="changeMode(mode)"
            :class="{'active btn-green': mode === value}">
            {{ $t(`scheduleMode_${mode}`) }}
        </button>
    </div>
</template>

<script>
    export default {
        name: 'schedule-form-chooser',
        props: ['value'],
        data() {
            return {
                // i18n:['scheduleMode_daily', 'scheduleMode_minutely', 'scheduleMode_once', 'scheduleMode_crontab']
                availableModes: ['daily', 'minutely', 'once', 'crontab'],
            };
        },
        methods: {
            changeMode(mode) {
                this.$emit('beforeChange', mode);
                this.$emit('input', mode);
            }
        }
    };
</script>

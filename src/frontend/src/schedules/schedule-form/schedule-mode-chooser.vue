<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-white"
            v-for="mode in availableModes"
            :key="mode"
            @click="changeMode(mode)"
            :class="{'active btn-green': mode === value}">
            {{ $t(mode) }}
        </button>
    </div>
</template>

<script>
    export default {
        name: 'schedule-form-chooser',
        props: ['value'],
        data() {
            return {
                availableModes: [
                    'daily', // i18n
                    'minutely', // i18n
                    'once', // i18n
                    'crontab', // i18n
                ]
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

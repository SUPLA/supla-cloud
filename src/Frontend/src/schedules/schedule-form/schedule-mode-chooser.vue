<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-white"
            v-for="mode in availableModes"
            @click="$emit('input', mode)"
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
                    'once', // i18n
                    'minutely', // i18n
                    'hourly', // i18n
                    'daily', // i18n
                ]
            };
        },
    };
</script>

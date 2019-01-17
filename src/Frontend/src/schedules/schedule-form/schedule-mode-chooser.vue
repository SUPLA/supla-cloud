<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-white"
            v-for="m in availableModes"
            @click="changeScheduleMode(m)"
            :class="{'active btn-green': m === mode}">
            {{ $t(m) }}
        </button>
    </div>
</template>

<script type="text/babel">
    import {mapMutations, mapState} from "vuex";

    export default {
        name: 'schedule-form-chooser',
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
        computed: mapState(['mode']),
        methods: mapMutations(['changeScheduleMode'])
    };
</script>

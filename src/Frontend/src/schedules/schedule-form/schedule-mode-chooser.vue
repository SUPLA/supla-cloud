<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-white"
            v-for="mode in availableModes"
            @click="changeScheduleMode(mode)"
            :class="{'active btn-green': scheduleMode == mode}">
            {{ $t(mode) }}
        </button>
    </div>
</template>

<script type="text/babel">
    import {mapMutations, mapState} from "vuex";
    export default {
        name: 'schedule-form-chooser',
        data () {
            return {
                availableModes: ['once', 'minutely', 'hourly', 'daily']
            };
        },
        computed: mapState(['scheduleMode']),
        methods: mapMutations(['changeScheduleMode'])
    };
</script>

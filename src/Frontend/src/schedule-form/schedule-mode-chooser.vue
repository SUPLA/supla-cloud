<template>
    <div class="btn-group btn-group-lg">
        <button class="btn btn-default"
                v-for="mode in availableModes"
                @click="changeScheduleMode(mode)"
                :class="{'active btn-success': scheduleMode == mode}">
            {{ mode }}
        </button>
    </div>
</template>

<script type="text/babel">
    import {mapMutations, mapState} from "vuex/src/helpers";
    export default {
        name: 'schedule-form-chooser',
        data () {
            return {
                availableModes: ['once', 'minutely', 'hourly', 'daily']
            }
        },
        computed: mapState(['scheduleMode']),
        methods: mapMutations(['changeScheduleMode'])
    }
</script>

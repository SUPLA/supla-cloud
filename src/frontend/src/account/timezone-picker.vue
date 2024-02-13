<template>
    <span class="timezone-picker">
        <SelectForSubjects
            class="timezones-dropdown"
            do-not-hide-selected
            :options="availableTimezones"
            :caption="(timezone) => `${timezone.name} (UTC${timezone.offset >= 0 ? '+' : ''}${timezone.offset}) ${timezone.currentTime}`"
            choose-prompt-i18n="choose the timezone"
            v-model="chosenTimezone"
            @input="updateTimezone"/>
    </span>
</template>

<script>
    import {DateTime, Settings} from "luxon";
    import TIMEZONES_LIST from "./timezones-list.json";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        components: {SelectForSubjects},
        props: ['timezone'],
        data() {
            return {chosenTimezone: undefined};
        },
        mounted() {
            this.chosenTimezone = this.timezone ? {id: this.timezone} : undefined;
        },
        methods: {
            updateTimezone() {
                Settings.defaultZone = this.chosenTimezone.id;
                this.$http.patch('users/current', {timezone: this.chosenTimezone.id, action: 'change:userTimezone'});
            },
        },
        computed: {
            availableTimezones() {
                return TIMEZONES_LIST.map(function (timezone) {
                    return {
                        id: timezone,
                        name: timezone,
                        offset: DateTime.now().setZone(timezone).offset / 60,
                        currentTime: DateTime.now().setZone(timezone).toLocaleString(DateTime.TIME_SIMPLE),
                    };
                }).sort(function (timezone1, timezone2) {
                    if (timezone1.offset == timezone2.offset) {
                        return timezone1.name < timezone2.name ? -1 : 1;
                    } else {
                        return timezone1.offset - timezone2.offset;
                    }
                });
            },
        },
    };
</script>

<template>
    <span class="timezone-picker">
        <select data-live-search="true"
            data-width="100%"
            v-model="chosenTimezone"
            @change="updateTimezone()"
            ref="dropdown">
            <option v-for="timezone in availableTimezones"
                :key="timezone.name"
                :value="timezone.name">
                {{ timezone.name }}
                (UTC{{ timezone.offset >= 0 ? '+' : '' }}{{ timezone.offset }})
                {{ timezone.currentTime }}
            </option>
        </select>
    </span>
</template>

<script>
    import Vue from "vue";
    import "@/common/bootstrap-select";
    import $ from "jquery";
    import {DateTime, Settings} from "luxon";
    import TIMEZONES_LIST from "./timezones-list.json";

    export default {
        props: ['timezone'],
        data() {
            return {chosenTimezone: undefined};
        },
        mounted() {
            this.chosenTimezone = this.timezone;
            this.initSelectPicker();
        },
        methods: {
            updateTimezone() {
                Settings.defaultZone = this.chosenTimezone;
                this.$http.patch('users/current', {timezone: this.chosenTimezone, action: 'change:userTimezone'});
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
            },
        },
        computed: {
            availableTimezones() {
                return TIMEZONES_LIST.map(function (timezone) {
                    return {
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
            selectOptions() {
                return {
                    noneSelectedText: this.$t('choose the timezone'),
                    liveSearchPlaceholder: this.$t('Search'),
                    noneResultsText: this.$t('No results match {0}'),
                };
            },
        },
        watch: {
            '$i18n.locale'() {
                $(this.$refs.dropdown).selectpicker('destroy');
                this.initSelectPicker();
            },
        },
    };
</script>

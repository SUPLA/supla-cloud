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
    import moment from "moment";
    import $ from "jquery";

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
                moment.tz.setDefault(this.chosenTimezone);
                this.$http.patch('users/current', {timezone: this.chosenTimezone, action: 'change:userTimezone'});
            },
            initSelectPicker() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker(this.selectOptions));
            },
        },
        computed: {
            availableTimezones() {
                return moment.tz.names().filter(function (timezone) {
                    return timezone.match(/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific|UTC)\//);
                }).map(function (timezone) {
                    return {
                        name: timezone,
                        offset: moment.tz(timezone).utcOffset() / 60,
                        currentTime: moment.tz(timezone).format('H:mm')
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

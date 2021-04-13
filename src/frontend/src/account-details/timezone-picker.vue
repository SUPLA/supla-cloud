<template>
    <span class="timezone-picker">
        <select data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            :data-none-selected-text="$t('choose the timezone')"
            :data-none-results-text="$t('No results match {0}')"
            data-width="100%"
            v-model="chosenTimezone"
            @change="updateTimezone()"
            ref="dropdown">
            <option v-for="timezone in availableTimezones"
                :value="timezone.name">
                {{ timezone.name }}
                (UTC{{timezone.offset >= 0 ? '+' : ''}}{{timezone.offset}})
                {{ timezone.currentTime }}
            </option>
        </select>
    </span>
</template>

<script>
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";

    export default {
        props: ['timezone'],
        data() {
            return {chosenTimezone: undefined};
        },
        mounted() {
            this.chosenTimezone = this.timezone;
            Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
        },
        methods: {
            updateTimezone() {
                moment.tz.setDefault(this.chosenTimezone);
                this.$http.patch('users/current', {timezone: this.chosenTimezone, action: 'change:userTimezone'});
            }
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
            }
        }
    };
</script>

<template>
    <span class="timezone-picker">
        <select data-placeholder="Wybierz strefę czasową" v-model="timezone" ref="dropdown">
            <option v-for="timezone in availableTimezones" :value="timezone.name">
                {{ timezone.name }}
                (UTC{{timezone.offset >= 0 ? '+' : ''}}{{timezone.offset}})
                {{ timezone.currentTime }}
            </option>
        </select>
    </span>
</template>

<script type="text/babel">
    import Vue from "vue";
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";

    export default {
        name: 'app',
        props: ['timezone'],
        mounted() {
            Vue.nextTick(() => $(this.$refs.dropdown).chosen({search_contains: true}).change((e) => {
                let timezone = e.currentTarget.value;
                moment.tz.setDefault(timezone);
                this.$http.put('account/user-timezone', {timezone});
            }));
        },
        computed: {
            availableTimezones() {
                return moment.tz.names().filter(function (timezone) {
                    return timezone.match(/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//);
                }).map(function (timezone) {
                    return {
                        name: timezone,
                        offset: moment.tz(timezone).utcOffset() / 60,
                        currentTime: moment.tz(timezone).format('H:mm')
                    }
                }).sort(function (timezone1, timezone2) {
                    if (timezone1.offset == timezone2.offset) {
                        return timezone1.name < timezone2.name ? -1 : 1;
                    } else {
                        return timezone1.offset - timezone2.offset
                    }
                });
            }
        }
    }
</script>

<style lang="scss" rel="stylesheet/scss">
    .timezone-picker {
        select {
            max-width: 100%;
        }
        .chosen-single {
            cursor: pointer;
            border: 0 !important;
            background: transparent !important;
            box-shadow: initial !important;
            padding: 0 !important;
            line-height: 25px !important;
            height: 25px !important;
        }
    }
</style>

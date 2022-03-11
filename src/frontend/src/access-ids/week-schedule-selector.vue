<template>
    <div>
        <table class="week-schedule-selector">
            <thead>
            <tr>
                <th></th>
                <!-- i18n: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
                <th v-for="weekday in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
                    :key="weekday"
                    class="weekday-header ellipsis">
                    {{ $t(weekday) }}
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="hour in [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]"
                :key="hour">
                <th scope="row"
                    class="hour-header">{{ hour }}:00 - {{ hour }}:59
                </th>
                <td v-for="weekday in [1,2,3,4,5,6,7]"
                    :key="'0' + hour + weekday">
                    <a class="time-slot">&nbsp;</a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        components: {},
        props: ['value'],
        data() {
            return {};
        },
        mounted() {
        },
        methods: {},
        computed: {},
        watch: {}
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    table.week-schedule-selector {
        user-select: none;
        table-layout: fixed;
        width: 100%;
        .weekday-header {
            text-align: center;
        }
        .hour-header {
            text-align: right;
            padding-right: 5px;
        }
        th {
            font-weight: normal;
        }
        tr {
            height: 1px; // allows for the links inside tds fill the whole height https://stackoverflow.com/a/34781198/878514
        }
        td {
            height: inherit;
            padding: 1px;
        }
        .time-slot {
            display: block;
            height: 100%;
            background: $supla-grey-light;
            &:hover {
                background: pink;
            }
        }
    }
</style>

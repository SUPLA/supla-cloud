<template>
    <div class="week-schedule-selector">
        <div></div>
        <!-- i18n: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
        <div v-for="weekday in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']"
            :key="weekday"
            class="weekday-header">
            {{ $t(weekday) }}
        </div>
        <template v-for="hour in [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]">
            <div :key="hour"
                class="hour-header">
                {{ hour }}:00 - {{ hour }}:59
            </div>
            <a v-for="weekday in [1,2,3,4,5,6,7]"
                class="time-slot"
                :key="'0' + hour + weekday"></a>
        </template>
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

    .week-schedule-selector {
        user-select: none;
        display: flex;
        flex-wrap: wrap;
        > * {
            flex: 1 0 12%;
            margin: 1px;
        }
        .weekday-header {
            text-align: center;
        }
        .hour-header {
            text-align: right;
            padding-right: 5px;
        }
        .time-slot {
            background: $supla-grey-light;
            &:hover {
                background: pink;
            }
        }
    }
</style>

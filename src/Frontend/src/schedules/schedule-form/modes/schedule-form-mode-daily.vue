<template>
    <div>
        <div class="form-group">
            <div class="btn-group btn-group-sm btn-group-justified">
                <a class="btn btn-default"
                    @click="hourChooseMode = 'normal'"
                    :class="{'active btn-green': hourChooseMode == 'normal'}">{{ $t('Chosen hour') }}</a>
                <a class="btn btn-default"
                    @click="hourChooseMode = 'sun'"
                    :class="{'active btn-green': hourChooseMode == 'sun'}">{{ $t('Sunrise / Sunset') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <schedule-form-mode-daily-hour :weekdays="weekdaysCronExpression"
                    v-if="hourChooseMode == 'normal'"></schedule-form-mode-daily-hour>
                <schedule-form-mode-daily-sun :weekdays="weekdaysCronExpression"
                    v-if="hourChooseMode == 'sun'"></schedule-form-mode-daily-sun>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ $t('Days') }}</label>
                    <div class="checkbox"
                        v-for="(weekday, index) in ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays']">
                        <label>
                            <input type="checkbox"
                                :value="index + 1"
                                v-model="weekdays">
                            {{ $t(weekday) }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import ScheduleFormModeDailyHour from "./schedule-form-mode-daily-hour.vue";
    import ScheduleFormModeDailySun from "./schedule-form-mode-daily-sun.vue";
    import {mapState} from "vuex";

    export default {
        name: 'schedule-form-mode-daily',
        components: {ScheduleFormModeDailyHour, ScheduleFormModeDailySun},
        data() {
            return {
                hourChooseMode: 'normal',
                weekdays: []
            };
        },
        beforeMount() {
            if (this.timeExpression) {
                let parts = this.timeExpression.split(' ');
                if (parts[4] != '*') {
                    this.weekdays = parts[4].split(',');
                }
                if (this.timeExpression[0] == 'S') {
                    this.hourChooseMode = 'sun';
                }
            }
        },
        computed: {
            weekdaysCronExpression() {
                if (this.weekdays.length == 0 || this.weekdays.length == 7) {
                    return '*';
                } else {
                    return this.weekdays.sort((a, b) => a - b).join(',');
                }
            },
            ...mapState(['timeExpression'])
        }
    };
</script>

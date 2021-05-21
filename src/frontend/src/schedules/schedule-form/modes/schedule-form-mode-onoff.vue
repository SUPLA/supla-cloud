<template>
    <div>
        <div class="form-group">
            <!-- i18n:['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
            <div class="input-group">
                <div class="input-group-btn"><a class="btn btn-default"
                    @click="nextDay(-1)">&lt;</a></div>
                <input type="text"
                    class="form-control text-center"
                    readonly
                    :value="$t(availableDays[currentDay])">
                <div class="input-group-btn"><a class="btn btn-default"
                    @click="nextDay()">&gt;</a></div>
            </div>
        </div>
        {{ config }}
        <div v-if="config[currentDay]">
            <div class="form-group"
                v-for="action in config[currentDay]">
                <div class="row">

                    <div class="col-md-6">
                        <schedule-form-mode-daily-hour
                            v-model="action.timeExpression"
                            :weekdays="[currentDay]"
                            v-if="action.type === 'hour'"></schedule-form-mode-daily-hour>
                        <schedule-form-mode-daily-sun
                            v-model="action.timeExpression"
                            :weekdays="[currentDay]"
                            v-if="action.type === 'sun'"></schedule-form-mode-daily-sun>
                    </div>
                    <div class="col-md-6">
                        <channel-action-chooser :subject="subject"
                            v-model="action.action"
                            :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">

            <h4>Dodaj akcję</h4>
            <div class="btn-group btn-group-justified">
                <a class="btn btn-default"
                    @click="addAction('hour')">
                    <i class="pe-7s-clock"></i>
                    Dla wskazanej godziny
                </a>
                <a class="btn btn-default"
                    @click="addAction('sun')">
                    <i class="pe-7s-sun"></i>
                    W oparciu o wschód / zachód
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import ScheduleFormModeDailyHour from "@/schedules/schedule-form/modes/schedule-form-mode-daily-hour";
    import ScheduleFormModeDailySun from "@/schedules/schedule-form/modes/schedule-form-mode-daily-sun";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser";

    export default {
        components: {ChannelActionChooser, ScheduleFormModeDailySun, ScheduleFormModeDailyHour},
        props: ['value', 'subject'],
        data() {
            return {
                currentDay: 0,
                config: {},
                availableDays: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'],
            };
        },
        methods: {
            updateTimeExpression() {
                this.minutes = Math.min(Math.max(this.roundTo5(this.minutes), 5), 300);
                const cronExpression = '*/' + this.minutes + ' * * * *';
                // this.$emit('input', cronExpression);
            },
            roundTo5(int) {
                return Math.round(Math.floor(int / 5) * 5);
            },
            nextDay(change = 1) {
                this.currentDay += change;
                if (this.currentDay >= this.availableDays.length) {
                    this.currentDay = 0;
                }
                if (this.currentDay < 0) {
                    this.currentDay = this.availableDays.length - 1;
                }
            },
            addAction(type) {
                if (!this.config[this.currentDay]) {
                    this.$set(this.config, this.currentDay, []);
                }
                this.config[this.currentDay].push({type});
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
        },
        mounted() {
            let currentMinutes = this.value && this.value.match(/^\*\/([0-9]+)/);
            if (currentMinutes) {
                this.minutes = +currentMinutes[1];
            }
            this.updateTimeExpression();
        },
        computed: {
            // theValue() {
            //     this.config
            // }
        }
    };
</script>

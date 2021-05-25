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
        <div v-if="config[currentDay]">
            <div class="daily-action"
                :key="action.tempId"
                v-for="action in config[currentDay]">
                <div class="row">
                    <div class="col-md-5">
                        <schedule-form-mode-daily-hour
                            v-model="action.crontab"
                            @input="updateConfig()"
                            :weekdays="[currentDay]"
                            v-if="action.type === 'hour'"></schedule-form-mode-daily-hour>
                        <schedule-form-mode-daily-sun
                            v-model="action.crontab"
                            @input="updateConfig()"
                            :weekdays="[currentDay]"
                            v-if="action.type === 'sun'"></schedule-form-mode-daily-sun>
                    </div>
                    <div class="col-md-7 vertical">
                        <channel-action-chooser :subject="subject"
                            v-model="action.action"
                            @input="updateConfig()"
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
    import {mapValues, toArray, flatten} from "lodash";
    import {generatePassword} from "@/common/utils";

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
            updateConfig() {
                this.$emit('input', this.theValue);
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
                this.config[this.currentDay].push({tempId: generatePassword(10, true), type});
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
        },
        mounted() {
            // this.crontab = this.value;
            // const parts = this.value.split(' ');
            // if (parts[4] != '*') {
            //     this.weekdays = parts[4].split(',');
            // }
            // if (this.value[0] == 'S') {
            //     this.hourChooseMode = 'sun';
            // }
        },
        computed: {
            theValue() {
                const mapped = mapValues(this.config, (array) => {
                    return array.map(({crontab, action}) => ({crontab, action}));
                });
                return flatten(toArray(mapped)).filter(({crontab, action}) => crontab && action && action.id);
            }
        }
    };
</script>

<style lang="scss">
    @import '../../../styles/variables';

    .daily-action {
        border-bottom: 1px solid $supla-green;
        padding-bottom: 1em;
        margin-bottom: 1.3em;
    }
</style>

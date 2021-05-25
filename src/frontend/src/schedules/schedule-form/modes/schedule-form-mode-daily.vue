<template>
    <div>
        <div class="form-group schedule-mode-daily-header">
            <!-- i18n:['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'] -->
            <div class="input-group">
                <div class="input-group-btn"><a class="btn btn-default"
                    @click="nextDay(-1)">&lt;</a></div>
                <input type="text"
                    class="form-control text-center"
                    readonly
                    :value="$t(availableDays[currentDay - 1])">
                <div class="input-group-btn"><a class="btn btn-default"
                    @click="nextDay()">&gt;</a></div>
            </div>
        </div>
        <div v-if="config[currentDay] && config[currentDay].length > 0">
            <div class="daily-action"
                :key="action.tempId"
                v-for="action in config[currentDay]">
                <div class="row">
                    <div class="col-sm-5">
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
                    <div class="col-sm-7 vertical">
                        <a @click="removeItem(action)"
                            class="remove-item-button">
                            <i class="pe-7s-close-circle"></i>
                        </a>
                        <channel-action-chooser :subject="subject"
                            v-model="action.action"
                            @input="updateConfig()"
                            :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="weekdaysWithActions.length"
            class="form-group">
            {{ $t('Copy actions from:') }}
            <ul>
                <li v-for="weekday in weekdaysWithActions"
                    :key="weekday">
                    <a @click="copyFrom(weekday)">
                        {{ $t(availableDays[weekday - 1]) }}
                    </a>
                </li>
            </ul>
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
    import {cloneDeep, pull} from "lodash";

    export default {
        components: {ChannelActionChooser, ScheduleFormModeDailySun, ScheduleFormModeDailyHour},
        props: ['value', 'subject'],
        data() {
            return {
                currentDay: 1,
                config: {},
                availableDays: ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'],
            };
        },
        methods: {
            updateConfig() {
                this.$emit('input', this.scheduleConfig);
            },
            roundTo5(int) {
                return Math.round(Math.floor(int / 5) * 5);
            },
            nextDay(change = 1) {
                this.currentDay += change;
                if (this.currentDay > this.availableDays.length) {
                    this.currentDay = 1;
                }
                if (this.currentDay < 1) {
                    this.currentDay = this.availableDays.length;
                }
            },
            addAction(type) {
                if (!this.config[this.currentDay]) {
                    this.$set(this.config, this.currentDay, []);
                }
                const action = {tempId: generatePassword(10, true), type, crontab: '', action: {}};
                this.config[this.currentDay].push(action);
                return action;
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
            copyFrom(weekday) {
                this.$set(this.config, this.currentDay, cloneDeep(this.config[weekday]));
            },
            removeItem(item) {
                const index = this.config[this.currentDay].indexOf(item);
                this.config[this.currentDay].splice(index, 1);
            }
        },
        mounted() {
            if (this.value) {
                this.value.forEach(({crontab, action}) => {
                    const parts = crontab.split(' ');
                    const type = parts[0].charAt(0) === 'S' ? 'sun' : 'hour';
                    const weekdayPart = parts.pop();
                    const weekdays = weekdayPart === '*' ? [1, 2, 3, 4, 5, 6, 7] : weekdayPart.split(',');
                    weekdays.forEach((weekday) => {
                        weekday = weekday == 0 ? 7 : weekday;
                        this.currentDay = weekday;
                        const addedAction = this.addAction(type);
                        addedAction.crontab = [...parts, weekday].join(' ');
                        addedAction.action = cloneDeep(action);
                    });
                });
                this.currentDay = 1;
            }
        },
        computed: {
            scheduleConfig() {
                const mapped = mapValues(this.config, (array) => {
                    return array.map(({crontab, action}) => ({crontab, action}));
                });
                return flatten(toArray(mapped)).filter(({crontab, action}) => crontab && action && action.id);
            },
            weekdaysWithActions() {
                return [1, 2, 3, 4, 5, 6, 7].filter((weekday) => this.config[weekday] && this.config[weekday].length > 0);
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

    .schedule-mode-daily-header {
        input {
            background: transparent !important;
            border: 0;
            font-size: 1.3em;
            color: $supla-black;
        }
    }

    .remove-item-button {
        font-weight: bold;
        font-size: 1.3em;
        position: absolute;
        right: 1em;
        top: -.5em;
        color: $supla-red;
    }
</style>

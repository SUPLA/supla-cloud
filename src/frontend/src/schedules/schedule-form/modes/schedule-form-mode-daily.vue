<template>
    <div>
        <schedule-form-mode-daily-day-selector
            v-if="weekdayGroups"
            :weekday-groups="weekdayGroups"
            @groups="weekdayGroups = $event"
            @groupIndex="weekdayGroupIndex = $event"></schedule-form-mode-daily-day-selector>
        {{ weekdayGroups }}
        {{ weekdayGroupIndex }}
        {{ scheduleConfig }}
        <div v-if="config[weekdayGroupIndex] && config[weekdayGroupIndex].length > 0">
            <div class="daily-action"
                :key="action.tempId"
                v-for="action in config[weekdayGroupIndex]">
                <div class="row">
                    <div class="col-sm-5">
                        <schedule-form-mode-daily-hour
                            v-model="action.crontab"
                            @input="updateConfig()"
                            :weekdays="weekdayGroups[weekdayGroupIndex]"
                            v-if="action.type === 'hour'"></schedule-form-mode-daily-hour>
                        <schedule-form-mode-daily-sun
                            v-model="action.crontab"
                            @input="updateConfig()"
                            :weekdays="weekdayGroups[weekdayGroupIndex]"
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
    import {flatten, mapValues, toArray} from "lodash";
    import {generatePassword} from "@/common/utils";
    import ScheduleFormModeDailyDaySelector from "@/schedules/schedule-form/modes/schedule-form-mode-daily-day-selector";
    import {cloneDeep} from "lodash";

    export default {
        components: {ScheduleFormModeDailyDaySelector, ChannelActionChooser, ScheduleFormModeDailySun, ScheduleFormModeDailyHour},
        props: ['value', 'subject'],
        data() {
            return {
                weekdayGroups: undefined,
                weekdayGroupIndex: 0,
                config: {},
            };
        },
        methods: {
            updateConfig() {
                this.$emit('input', this.scheduleConfig);
            },
            addAction(type) {
                if (!this.config[this.weekdayGroupIndex]) {
                    this.$set(this.config, this.weekdayGroupIndex, []);
                }
                const action = {tempId: generatePassword(10, true), type, crontab: '', action: {}};
                this.config[this.weekdayGroupIndex].push(action);
                return action;
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
            removeItem(item) {
                const index = this.config[this.weekdayGroupIndex].indexOf(item);
                this.config[this.weekdayGroupIndex].splice(index, 1);
                this.updateConfig();
            }
        },
        mounted() {
            if (this.value) {
                this.weekdayGroups = [];
                this.value.forEach(({crontab, action}) => {
                    const parts = crontab.split(' ');
                    const type = parts[0].charAt(0) === 'S' ? 'sun' : 'hour'
                    const weekdayPart = parts[parts.length - 1];
                    this.weekdayGroupIndex = this.weekdayGroups.indexOf(weekdayPart);
                    if (this.weekdayGroupIndex === -1) {
                        this.weekdayGroupIndex = this.weekdayGroups.length;
                        this.weekdayGroups.push(weekdayPart);
                    }
                    const addedAction = this.addAction(type);
                    addedAction.crontab = crontab;
                    addedAction.action = cloneDeep(action);
                });
                this.weekdayGroupIndex = 0;
            } else {
                this.weekdayGroups = ['*'];
            }
            // if (this.value) {
            //     this.value.forEach(({crontab, action}) => {
            //         const parts = crontab.split(' ');
            //         const type = parts[0].charAt(0) === 'S' ? 'sun' : 'hour';
            //         const weekdayPart = parts.pop();
            //         const weekdays = weekdayPart === '*' ? [1, 2, 3, 4, 5, 6, 7] : weekdayPart.split(',');
            //         weekdays.forEach((weekday) => {
            //             weekday = weekday == 0 ? 7 : weekday;
            //             this.currentDay = weekday;
            //             const addedAction = this.addAction(type);
            //             addedAction.crontab = [...parts, weekday].join(' ');
            //             addedAction.action = cloneDeep(action);
            //         });
            //     });
            //     this.currentDay = 1;
            // }
        },
        computed: {
            scheduleConfig() {
                const mapped = mapValues(this.config, (array) => {
                    return array.map(({crontab, action}) => ({crontab, action}));
                });
                return flatten(toArray(mapped)).filter(({crontab, action}) => crontab && action && action.id);
            },
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

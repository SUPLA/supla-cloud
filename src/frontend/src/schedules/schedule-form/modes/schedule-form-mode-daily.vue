<template>
    <div>
        <schedule-form-mode-daily-day-selector
            v-if="weekdayGroups"
            :weekday-groups="weekdayGroups"
            @groups="weekdayGroups = $event"
            @groupIndex="weekdayGroupIndex = $event"
            @groupIndexRemove="removeWeekdayGroup($event)"></schedule-form-mode-daily-day-selector>
        <div v-if="config[weekdayGroupIndex] && config[weekdayGroupIndex].length > 0">
            <div class="schedule-action"
                :key="action.tempId"
                v-for="action in config[weekdayGroupIndex]">
                <div class="schedule-action-row">
                    <div class="schedule-action-time-chooser">
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
                    <div class="schedule-action-actions">
                        <channel-action-chooser :subject="subject"
                            v-model="action.action"
                            @input="updateConfig()"
                            :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                    </div>
                    <div class="schedule-action-buttons">
                        <a @click="removeItem(action)"
                            class="remove-item-button">
                            <i class="pe-7s-close-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <h4>{{ $t('Add action') }}</h4>
            <div class="btn-group btn-group-justified">
                <a class="btn btn-default btn-wrapped"
                    @click="addAction('hour')">
                    <i class="pe-7s-clock"></i>
                    {{ $t('At specified time') }}
                </a>
                <a class="btn btn-default btn-wrapped"
                    @click="addAction('sun')">
                    <i class="pe-7s-sun"></i>
                    {{ $t('Based on sunrise or sunset') }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import ScheduleFormModeDailyHour from "@/schedules/schedule-form/modes/schedule-form-mode-daily-hour";
    import ScheduleFormModeDailySun from "@/schedules/schedule-form/modes/schedule-form-mode-daily-sun";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser";
    import {cloneDeep, flatten, toArray} from "lodash";
    import {generatePassword} from "@/common/utils";
    import ScheduleFormModeDailyDaySelector from "@/schedules/schedule-form/modes/schedule-form-mode-daily-day-selector";
    import ChannelFunctionAction from "../../../common/enums/channel-function-action";

    export default {
        components: {ScheduleFormModeDailyDaySelector, ChannelActionChooser, ScheduleFormModeDailySun, ScheduleFormModeDailyHour},
        props: ['value', 'subject'],
        data() {
            return {
                weekdayGroups: undefined,
                weekdayGroupIndex: 0,
                config: [],
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
                return ChannelFunctionAction.availableInSchedules(possibleAction.id);
            },
            removeItem(item) {
                const index = this.config[this.weekdayGroupIndex].indexOf(item);
                this.config[this.weekdayGroupIndex].splice(index, 1);
                this.updateConfig();
            },
            removeWeekdayGroup(index) {
                this.config.splice(index, 1);
            },
        },
        mounted() {
            if (this.value && this.value.length) {
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
        },
        computed: {
            scheduleConfig() {
                const mapped = this.config.map((array) => {
                    return array.map(({crontab, action}) => ({crontab, action}));
                });
                return flatten(toArray(mapped)).filter(({crontab, action}) => crontab && action && action.id);
            },
        }
    };
</script>

<style lang="scss">
    @import '../../../styles/variables';

    .daily-checkboxes {
        display: flex;
        justify-content: space-between;
    }

    .schedule-mode-daily-header {
        input {
            background: transparent !important;
            border: 0;
            font-size: 1.3em;
            color: $supla-black;
        }
    }
</style>

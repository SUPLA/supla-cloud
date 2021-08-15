<template>
    <div>
        <div class="form-group">
            <a class="btn btn-block btn-link"
                @click="showHelp = !showHelp">
                Instrukcja
            </a>
            <transition-expand>
                <div class="well"
                    v-if="showHelp">
                    <p class="text-justify">{{ $t('This schedule mode is meant to be used by advanced or patient users only. You need to specify schedule execution behavior in a Crontab notation (extended by some SUPLA flavours). You may find the crontab.guru website helpful, if you have not came across crontabs yet.') }}</p>
                    <p class="text-center"><a href="https://crontab.guru/">https://crontab.guru</a></p>
                    <p class="text-justify">{{ $t('Keep in mind, that the humanized description of the entered crontab is not perfect. It is meant to help you find appropriate value, not to validate your input. Pay attention to the "Closest executions" section for final confiration that the schedule you configured behaves as expected.') }}</p>
                    <p class="text-justify">{{ $t('You may find the below examples helpful, provided that you are still willing to use the advanced schedule mode.') }}</p>
                    <ul>
                        <li><code>*/5 * 30 5 *</code> - {{ $t('At every 5th minute on day-of-month 30 in May') }}</li>
                        <li><code>30 11 * * WED#3</code> - {{ $t('At 11:30 AM, on the third Wednesday of the month') }}</li>
                        <li><code>45 17 L * *</code> - {{ $t('At 05:45 PM, on the last day of the month') }}</li>
                        <li><code>SR0 * * * 1-5</code> - {{ $t('At sunrise, Monday through Friday') }}</li>
                        <li>
                            <code>SS-10 * 10 MAY-AUG *</code> - {{ $t('10 minutes before sunset, on day 10 of the month, May through August') }}
                        </li>
                        <li><code>SR15 * */2 * *</code> - {{ $t('15 minutes after sunrise, every 2 days') }}</li>
                    </ul>
                </div>
            </transition-expand>
        </div>
        <div class="crontab-action"
            :key="action.tempId"
            v-for="action in config">
            <a @click="removeItem(action)"
                class="remove-item-button">
                <i class="pe-7s-close-circle"></i>
            </a>
            <br>
            <schedule-form-mode-crontab-input v-model="action.crontab"
                @input="updateConfig()"></schedule-form-mode-crontab-input>
            <channel-action-chooser :subject="subject"
                v-model="action.action"
                @input="updateConfig()"
                :possible-action-filter="possibleActionFilter"></channel-action-chooser>
        </div>
        <div class="form-group">
            <a class="btn btn-default btn-block"
                @click="addAction()">
                <i class="pe-7s-clock"></i>
                Dodaj
            </a>
        </div>
    </div>
</template>

<script>
    import ChannelActionChooser from "@/channels/action/channel-action-chooser";
    import {generatePassword} from "@/common/utils";
    import ScheduleFormModeCrontabInput from "@/schedules/schedule-form/modes/schedule-form-mode-crontab-input";
    import {cloneDeep} from "lodash";
    import TransitionExpand from "@/common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ScheduleFormModeCrontabInput, ChannelActionChooser},
        props: ['value', 'subject'],
        data() {
            return {
                config: [],
                showHelp: false,
            };
        },
        methods: {
            updateConfig() {
                this.$emit('input', this.scheduleConfig);
            },
            addAction() {
                const action = {tempId: generatePassword(10, true), crontab: '', action: {}};
                this.config.push(action);
                return action;
            },
            removeItem(item) {
                const index = this.config.indexOf(item);
                this.config.splice(index, 1);
                this.updateConfig();
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
        },
        mounted() {
            if (this.value) {
                this.config = cloneDeep(this.value);
            }
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
                const mapped = this.config.map(({crontab, action}) => ({crontab, action}));
                return mapped.filter(({crontab, action}) => crontab && action && action.id);
            },
        }
    };
</script>

<style lang="scss">
    @import '../../../styles/variables';

    .crontab-action {
        border-bottom: 1px solid $supla-green;
        padding-bottom: 1em;
        margin-bottom: 1.3em;
        position: relative;
    }
</style>
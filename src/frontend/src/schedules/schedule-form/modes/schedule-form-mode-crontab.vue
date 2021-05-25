<template>
    <div>

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
    import {cloneDeep, pull} from "lodash";

    export default {
        components: {ScheduleFormModeCrontabInput, ChannelActionChooser},
        props: ['value', 'subject'],
        data() {
            return {
                config: [],
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

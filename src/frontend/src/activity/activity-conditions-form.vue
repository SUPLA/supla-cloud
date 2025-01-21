<template>
    <div>
        <h3 class="text-center">
            {{ $t('Activity conditions') }}
            <a @click="showConditionsHelp = !showConditionsHelp">
                <fa icon="info-circle" class="ml-2 small"/>
            </a>
        </h3>
        <transition-expand>
            <div v-if="showConditionsHelp" class="alert alert-info">
                {{ $t('The item will be active when all of the conditions will be meet. If you choose to set all of the available time settings, the item will be active when the time is between active from and active to, is within the selected working schedule and meets one of the daytime criteria.') }}
            </div>
        </transition-expand>
        <DateRangePicker v-model="activeDateRange"
            :label-date-start="$t('Active from')"
            :label-date-end="$t('Active to')"
            @input="onChanged()"/>
        <div class="form-group text-center">
            <label>
                <label class="checkbox2 checkbox2-grey">
                    <input type="checkbox" v-model="useWorkingSchedule" @change="onChanged()">
                    {{ $t('Use working schedule') }}
                </label>
            </label>
        </div>
        <transition-expand>
            <WeekScheduleSelector v-if="useWorkingSchedule" class="narrow mode-1-green"
                v-model="activeHours"
                @input="onChanged()"/>
        </transition-expand>
        <DaytimeActivityConditions class="mt-4" v-model="activityConditions" @input="onChanged()"/>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {deepCopy} from "@/common/utils";
    import DateRangePicker from "@/activity/date-range-picker.vue";
    import WeekScheduleSelector from "@/activity/week-schedule-selector.vue";
    import {mapValues, pickBy} from "lodash";
    import DaytimeActivityConditions from "@/activity/daytime-activity-conditions.vue";

    export default {
        components: {
            DaytimeActivityConditions,
            WeekScheduleSelector,
            DateRangePicker,
            TransitionExpand,
        },
        props: {
            value: Object,
        },
        data() {
            return {
                showConditionsHelp: false,
                activeDateRange: {},
                useWorkingSchedule: false,
                activeHours: {},
                activityConditions: [],
            };
        },
        beforeMount() {
            this.initFromItem();
        },
        methods: {
            onChanged() {
                this.$emit('input', this.modelValue);
            },
            initFromItem() {
                const item = deepCopy(this.value);
                this.activeDateRange = {dateStart: item.activeFrom, dateEnd: item.activeTo};
                if (item.activeHours) {
                    this.activeHours = mapValues(item.activeHours, (hours) => {
                        const hoursDef = {};
                        [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                        return hoursDef;
                    });
                    this.useWorkingSchedule = true;
                } else {
                    this.activeHours = {};
                    this.useWorkingSchedule = false;
                }
                this.activityConditions = item.activityConditions;
            },
        },
        computed: {
            modelValue() {
                return {
                    activeFrom: this.activeDateRange.dateStart || null,
                    activeTo: this.activeDateRange.dateEnd || null,
                    activeHours: this.useWorkingSchedule ? mapValues(this.activeHours, (hours) => {
                        return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
                    }) : null,
                    activityConditions: this.activityConditions,
                };
            },
        },
        watch: {
            value() {
                this.initFromItem();
            }
        }
    }
</script>

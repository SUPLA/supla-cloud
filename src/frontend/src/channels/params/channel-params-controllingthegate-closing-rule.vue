<template>
    <div class="channel-params-closing-rule">
        <h4 class="text-center">{{ $t("Automatic closing") }}</h4>
        <dl>
            <dd>{{ $t('Enabled') }}</dd>
            <dt class="text-center">
                <toggler v-model="props.channel.config.gateClosingRule.enabled" @input="$emit('change')"/>
            </dt>
            <dd>{{ $t('Maximum open time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="5"
                        max="3600"
                        class="form-control text-center"
                        v-model="props.channel.config.gateClosingRule.maxTimeOpen"
                        @change="$emit('change')">
                    <span class="input-group-addon">{{ $t('sec.') }}</span>
                </span>
            </dt>
            <dd>{{ $t('Working schedule') }}</dd>
            <dt>
                <WeekScheduleCaption :schedule="props.channel.config.gateClosingRule.activeHours" :emptyCaption="$t('everyday')"/>
                <div><a @click="changing = true">{{ $t('Change') }}</a></div>
            </dt>
        </dl>
        <modal v-if="changing"
            :header="$t('Automatic closing rule')"
            class="modal-800"
            @confirm="(changing = false) || $emit('change')">
            <DateRangePicker v-model="activeDateRange"/>
            <WeekScheduleSelector v-if="changing" v-model="activeHours"/>
        </modal>
    </div>
</template>

<script setup>
    import {computed, ref, set} from "vue";
    import DateRangePicker from "@/direct-links/date-range-picker";
    import WeekScheduleSelector from "@/access-ids/week-schedule-selector";
    import {mapValues, pickBy} from "lodash";
    import WeekScheduleCaption from "@/access-ids/week-schedule-caption";

    const props = defineProps({channel: Object});
    const changing = ref(false);

    const activeDateRange = computed({
        get() {
            return {dateStart: props.channel.config.gateClosingRule.activeFrom, dateEnd: props.channel.config.gateClosingRule.activeTo};
        },
        set(dates) {
            set(props.channel.config.gateClosingRule, 'activeFrom', dates.dateStart || null);
            set(props.channel.config.gateClosingRule, 'activeTo', dates.dateEnd || null);
        }
    });

    const activeHours = computed({
        get() {
            if (props.channel.config.gateClosingRule.activeHours) {
                return mapValues(props.channel.config.gateClosingRule.activeHours, (hours) => {
                    const hoursDef = {};
                    [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                    return hoursDef;
                });
            } else {
                return {};
            }
        },
        set(weekSchedule) {
            props.channel.config.gateClosingRule.activeHours = mapValues(weekSchedule, (hours) => {
                return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
            });
        }
    });
</script>

<style lang="scss">
    .channel-params-closing-rule {
        .schedule-block {
            display: block;
            &::after {
                content: '';
            }
        }
    }
</style>

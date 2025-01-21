<template>
    <div class="channel-params-closing-rule">
        <h4 class="text-center">{{ $t("Close automatically") }}</h4>
        <dl>
            <dd>{{ $t('Enabled') }}</dd>
            <dt class="text-center">
                <toggler v-model="props.channel.config.closingRule.enabled" @input="$emit('change')"/>
            </dt>
            <dd v-tooltip="$t('channelConfigHelp_gateCloseAfterHelp')">
                {{ $t('Close after') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="5"
                        max="480"
                        class="form-control text-center"
                        v-model="maxTimeOpenMin"
                        @change="$emit('change')">
                    <span class="input-group-addon">{{ $t('min.') }}</span>
                </span>
            </dt>
            <dd>{{ $t('Working schedule') }}</dd>
            <dt>
                <WeekScheduleCaption :schedule="props.channel.config.closingRule.activeHours" :emptyCaption="$t('everyday')"/>
                <div><a @click="startEdit()">{{ $t('Change') }}</a></div>
            </dt>
        </dl>
        <modal v-if="changing"
            :header="$t('Automatic closing rule')"
            class="modal-800"
            :cancellable="true"
            @cancel="changing = false"
            @confirm="confirmEdit()">
            <DateRangePicker v-model="activeDateRange"/>
            <WeekScheduleSelector v-model="activeHours" class="mode-1-green"/>
        </modal>
    </div>
</template>

<script setup>
    import {computed, ref, set} from "vue";
    import DateRangePicker from "@/activity/date-range-picker";
    import WeekScheduleSelector from "@/activity/week-schedule-selector.vue";
    import {mapValues, pickBy} from "lodash";
    import WeekScheduleCaption from "@/activity/week-schedule-caption.vue";

    const props = defineProps({channel: Object});
    const emit = defineEmits(['change'])

    const changing = ref(false);

    const maxTimeOpenMin = computed({
        get() {
            return Math.floor(props.channel.config.closingRule.maxTimeOpen / 60);
        },
        set(value) {
            set(props.channel.config.closingRule, 'maxTimeOpen', value * 60);
        }
    });

    const activeDateRange = ref();
    const activeHours = ref();

    const startEdit = () => {
        changing.value = true;
        activeDateRange.value = {
            dateStart: props.channel.config.closingRule.activeFrom,
            dateEnd: props.channel.config.closingRule.activeTo
        };
        if (props.channel.config.closingRule.activeHours) {
            activeHours.value = mapValues(props.channel.config.closingRule.activeHours, (hours) => {
                const hoursDef = {};
                [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                return hoursDef;
            });
        } else {
            activeHours.value = {};
        }
    };

    const confirmEdit = () => {
        set(props.channel.config.closingRule, 'activeFrom', activeDateRange.value.dateStart || null);
        set(props.channel.config.closingRule, 'activeTo', activeDateRange.value.dateEnd || null);
        set(props.channel.config.closingRule, 'activeHours', mapValues(activeHours.value, (hours) => {
            return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
        }));
        changing.value = false;
        emit('change');
    };
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

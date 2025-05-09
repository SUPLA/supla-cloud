<template>
    <div>
        <dl>
            <dd class="valign-top">{{ $t('Level sensors') }}</dd>
            <dt>
                <div class="mb-3" v-if="levelSensorsDef.length > 0">
                    <div class="d-flex align-items-center bottom-border py-2" v-for="lvl in levelSensorsDef" :key="lvl.id">
                        <div class="flex-grow-1">
                            <h5 class="my-1">
                                {{ channelTitle(channelsStore.all[lvl.channelId]) }}
                            </h5>
                            <dl>
                                <dt>{{ $t('Fill level') }}</dt>
                                <dd>
                                    <NumberInput v-model="lvl.fillLevel"
                                        :min="1"
                                        :max="100"
                                        suffix=" %"
                                        class="form-control text-center mt-2"
                                        @input="levelChanged()"/>
                                </dd>
                            </dl>
                        </div>
                        <div class="pl-3">
                            <a class="text-default" @click="handleRemoveSensor(lvl)">
                                <fa icon="trash"/>
                            </a>
                        </div>
                    </div>
                </div>
                <span class="small">{{ $t('Choose many') }}</span>
                <ChannelsDropdown @input="handleNewSensor" :hideNone="true"
                    :params="{skipIds: levelSensorsIds, deviceIds: channel.iodeviceId, fnc: 'CONTAINER_LEVEL_SENSOR'}"
                    :filter="onlyFreeSensors"/>
            </dt>
        </dl>
        <!-- i18n: ['tank_alarm_warningAboveLevel', 'tank_alarm_alarmAboveLevel', 'tank_alarm_warningBelowLevel', 'tank_alarm_alarmBelowLevel'] -->
        <!-- i18n: ['tank_alarm_fullRange_warningAboveLevel', 'tank_alarm_fullRange_alarmAboveLevel', 'tank_alarm_fullRange_warningBelowLevel', 'tank_alarm_fullRange_alarmBelowLevel'] -->
        <dl v-for="alarm in availableAlarms" :key="alarm">
            <dd v-if="fillLevelInFullRange">{{ $t(`tank_alarm_fullRange_${alarm}`) }}</dd>
            <dd v-else>{{ $t(`tank_alarm_${alarm}`) }}</dd>
            <dt>
                <div class="d-flex align-items-center ml-2" v-if="fillLevelInFullRange">
                    <label class="checkbox2 checkbox2-grey">
                        <input type="checkbox" :checked="channel.config[alarm] !== null"
                            @change="channel.config[alarm] = channel.config[alarm] === null ? 0 : null; emit('change')">
                    </label>
                    <NumberInput
                        v-if="channel.config[alarm] !== null"
                        v-model="channel.config[alarm]"
                        :min="0"
                        :max="100"
                        suffix=" %"
                        class="form-control text-center"
                        @input="emit('change')"/>
                    <input type="text" class="form-control text-center" disabled :placeholder="$t('Disabled')" v-else/>
                </div>
                <div class="dropdown" v-else-if="availableFillLevelsPerAlarm[alarm].length > 0">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                        type="button"
                        data-toggle="dropdown">
                        <span v-if="channel.config[alarm] != null">{{ rangeLabel(channel.config[alarm], alarm) }}</span>
                        <span v-else>{{ $t('Disabled') }}</span>
                        {{ ' ' }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a @click="channel.config[alarm] = null; emit('change')"
                                v-show="channel.config[alarm] !== null">{{ $t('Disabled') }}</a>
                        </li>
                        <li v-for="percent in availableFillLevelsPerAlarm[alarm]" :key="percent">
                            <a @click="channel.config[alarm] = percent; emit('change')"
                                v-show="percent !== channel.config[alarm]">{{ rangeLabel(percent, alarm) }}</a>
                        </li>
                    </ul>
                </div>
                <button v-else class="btn btn-default disabled btn-block btn-wrapped" type="button">{{ $t('Disabled') }}</button>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Mute alarm sound without additional auth') }}</dd>
            <dt class="text-center">
                <Toggler v-model="channel.config.muteAlarmSoundWithoutAdditionalAuth" @input="$emit('change')"/>
            </dt>
        </dl>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelsDropdown from "@/devices/channels-dropdown.vue";
    import {channelTitle} from "@/common/filters";
    import NumberInput from "@/common/number-input.vue";
    import {uniq} from "lodash";
    import Toggler from "../../common/gui/toggler";
    import {useChannelsDependenciesStore} from "@/stores/channels-dependencies-store";

    const props = defineProps({channel: Object});
    const emit = defineEmits('change');

    const channelsStore = useChannelsStore();
    const dependenciesStore = useChannelsDependenciesStore();

    const levelSensorsIds = computed(() => props.channel.config.levelSensors?.map(s => s.channelId) || []);
    const levelSensorsDef = computed({
        get() {
            return props.channel.config.levelSensors;
        },
        set(value) {
            props.channel.config.levelSensors = value;
            emit('change');
        }
    });
    const availableFillLevels = computed(() => uniq([0, ...levelSensorsDef.value.map(def => +def.fillLevel)]).sort((a, b) => a - b));
    const fillLevelInFullRange = computed(() => props.channel.config.fillLevelReportingInFullRange);

    const availableFillLevelsPerAlarm = computed(() => ({
        alarmAboveLevel: availableFillLevels.value.filter((fl) => fl > (props.channel.config.warningAboveLevel || props.channel.config.warningBelowLevel || props.channel.config.alarmBelowLevel || 0)),
        warningAboveLevel: availableFillLevels.value.filter((fl) => fl > (props.channel.config.warningBelowLevel || props.channel.config.alarmBelowLevel || 0) && fl < (props.channel.config.alarmAboveLevel || 101)),
        warningBelowLevel: availableFillLevels.value.filter((fl) => fl !== props.channel.config.alarmBelowLevel && fl > (props.channel.config.alarmBelowLevel || -1) && fl < (props.channel.config.warningAboveLevel || props.channel.config.alarmAboveLevel || availableFillLevels.value[availableFillLevels.value.length - 1])).reverse(),
        alarmBelowLevel: availableFillLevels.value.filter((fl) => fl !== props.channel.config.warningBelowLevel && fl < (props.channel.config.warningBelowLevel || props.channel.config.warningAboveLevel || props.channel.config.alarmAboveLevel || availableFillLevels.value[availableFillLevels.value.length - 1])).reverse(),
    }));

    const availableAlarms = ['alarmAboveLevel', 'warningAboveLevel', 'warningBelowLevel', 'alarmBelowLevel'];

    function levelChanged() {
        availableAlarms.forEach(alarm => {
            if (props.channel.config[alarm] != null && !availableFillLevels.value.includes(props.channel.config[alarm])) {
                props.channel.config[alarm] = null;
            }
        });
        emit('change');
    }

    function handleNewSensor(newSensor) {
        levelSensorsDef.value = [...levelSensorsDef.value, {channelId: newSensor.id, fillLevel: 1}];
    }

    function handleRemoveSensor(sensorToRemove) {
        levelSensorsDef.value = levelSensorsDef.value.filter(ch => ch.channelId !== sensorToRemove.channelId);
        levelChanged();
    }

    function onlyFreeSensors(sensor) {
        return dependenciesStore.forChannel(sensor.id)
            .filter(dep => dep.role === 'levelSensorChannelIds')
            .length === 0;
    }

    function rangeLabel(range, alarm) {
        if (range === 100) {
            return `${range}%`;
        }
        if (alarm.indexOf('Above') > 0) {
            return `${range}% - 100%`;
        } else {
            const rangeIndex = availableFillLevels.value.indexOf(range);
            const nextRange = availableFillLevels.value.length > rangeIndex + 1 ? availableFillLevels.value[rangeIndex + 1] - 1 : 100;
            return nextRange === 0 ? '0%' : `${nextRange}% - 0%`;
        }
    }

</script>

<style lang="scss" scoped>
    @import "../../styles/variables";

    .bottom-border {
        border-bottom: 1px solid $supla-grey-light;
        &:last-child {
            border: 0;
        }
    }
</style>

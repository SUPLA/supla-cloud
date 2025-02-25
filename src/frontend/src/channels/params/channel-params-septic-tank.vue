<template>
    <div>
        <dl>
            <dd class="valign-top">{{ $t('Level sensors') }}</dd>
            <dt>
                <div class="mb-3">
                    <div class="d-flex align-items-center bottom-border py-2" v-for="lvl in levelSensorsDef" :key="lvl.id">
                        <div class="flex-grow-1">
                            <h5 class="my-1">
                                {{ channelTitle(channelsStore.all[lvl.channelId]) }}
                            </h5>
                            <dl>
                                <dt>{{ $t('Fill level') }}</dt>
                                <dd>
                                    <NumberInput v-model="lvl.fillLevel"
                                        :min="0"
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
                    :params="{skipIds: levelSensorsIds, deviceIds: channel.iodeviceId, fnc: 'CONTAINER_LEVEL_SENSOR'}"/>
            </dt>
        </dl>
        <!-- i18n: ['tank_alarm_warningAboveLevel', 'tank_alarm_alarmAboveLevel', 'tank_alarm_warningBelowLevel', 'tank_alarm_alarmBelowLevel'] -->
        <dl v-for="alarm in availableAlarms" :key="alarm">
            <dd>{{ $t(`tank_alarm_${alarm}`) }}</dd>
            <dt>
                <NumberInput
                    v-model="channel.config[alarm]"
                    v-if="fillLevelInFullRange"
                    :min="0"
                    :max="100"
                    suffix=" %"
                    class="form-control text-center mt-2"
                    @input="emit('change')"/>
                <div class="dropdown" v-else>
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                        type="button"
                        data-toggle="dropdown">
                        <span v-if="channel.config[alarm] != null">{{ channel.config[alarm] }} %</span>
                        <span v-else>{{ $t('Disabled') }}</span>
                        {{ ' ' }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a @click="channel.config[alarm] = null; emit('change')"
                                v-show="channel.config[alarm] !== null">{{ $t('Disabled') }}</a>
                        </li>
                        <li v-for="percent in availableFillLevels" :key="percent">
                            <a @click="channel.config[alarm] = percent; emit('change')"
                                v-show="percent !== channel.config[alarm]">{{ percent }}%</a>
                        </li>
                    </ul>
                </div>
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

    const props = defineProps({channel: Object});
    const emit = defineEmits('change');

    const channelsStore = useChannelsStore();

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

    const availableAlarms = ['warningAboveLevel', 'alarmAboveLevel', 'warningBelowLevel', 'alarmBelowLevel'];

    function levelChanged() {
        availableAlarms.forEach(alarm => {
            if (props.channel.config[alarm] != null && !availableFillLevels.value.includes(props.channel.config[alarm])) {
                props.channel.config[alarm] = null;
            }
        });
        emit('change');
    }

    function handleNewSensor(newSensor) {
        levelSensorsDef.value = [...levelSensorsDef.value, {channelId: newSensor.id, fillLevel: 0}];
    }

    function handleRemoveSensor(sensorToRemove) {
        levelSensorsDef.value = levelSensorsDef.value.filter(ch => ch.channelId !== sensorToRemove.channelId);
        levelChanged();
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

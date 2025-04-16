<template>
    <div>
        <dl v-if="supportFloodSensors">
            <dd class="valign-top">{{ $t('Flood sensors') }}</dd>
            <dt>
                <div class="mb-3">
                    <div class="d-flex align-items-center bottom-border py-2" v-for="channel in floodSensors" :key="channel.id">
                        <div class="flex-grow-1">
                            <h5 class="my-1">
                                {{ channelTitle(channel) }}
                            </h5>
                        </div>
                        <div class="pl-3">
                            <a class="text-default" @click="handleRemoveSensor(channel)">
                                <fa icon="trash"/>
                            </a>
                        </div>
                    </div>
                </div>
                <span class="small">{{ $t('Choose many') }}</span>
                <ChannelsDropdown @input="handleNewSensor" :hideNone="true"
                    :params="{skipIds: floodSensorsIds, deviceIds: channel.iodeviceId, fnc: 'FLOOD_SENSOR'}"/>
            </dt>
        </dl>
        <dl v-if="channel.config.closeValveOnFloodType">
            <dd class="valign-top">
                {{ $t('Auto-close on flood') }}
                <a @click="closeValveOnFloodTypeHelpShown = !closeValveOnFloodTypeHelpShown"><i class="pe-7s-help1"></i></a>
            </dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                        {{ $t(`closeValveOnFloodType_${channel.config.closeValveOnFloodType}`) }}
                        <span class="caret"></span>
                    </button>
                    <!-- i18n:['closeValveOnFloodType_1', 'closeValveOnFloodType_2'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in [1, 2]" :key="type">
                            <a @click="channel.config.closeValveOnFloodType = type; $emit('change')"
                                v-show="type !== channel.config.closeValveOnFloodType">
                                {{ $t(`closeValveOnFloodType_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
                <transition-expand>
                    <div class="well small text-muted p-2 mt-2 display-newlines" v-if="closeValveOnFloodTypeHelpShown">
                        {{ $t('closeValveOnFloodType_help') }}
                    </div>
                </transition-expand>
            </dt>
        </dl>
    </div>
</template>

<script setup>
    import {computed, ref} from "vue";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelsDropdown from "@/devices/channels-dropdown.vue";
    import {channelTitle} from "@/common/filters";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    const props = defineProps({channel: Object});
    const emit = defineEmits('change');

    const closeValveOnFloodTypeHelpShown = ref(false);

    const channelsStore = useChannelsStore();

    const supportFloodSensors = computed(() => props.channel.config.floodSensorChannelIds !== undefined);
    const floodSensorsIds = computed(() => props.channel.config.floodSensorChannelIds || []);
    const floodSensors = computed({
        get() {
            return floodSensorsIds.value.map(id => channelsStore.all[id]);
        },
        set(value) {
            props.channel.config.floodSensorChannelIds = value.map(c => c.id);
            emit('change');
        }
    });

    function handleNewSensor(newSensor) {
        floodSensors.value = [...floodSensors.value, newSensor];
    }

    function handleRemoveSensor(sensorToRemove) {
        floodSensors.value = floodSensors.value.filter(ch => ch.id !== sensorToRemove.id);
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

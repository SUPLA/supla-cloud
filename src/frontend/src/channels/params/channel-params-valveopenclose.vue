<template>
    <div>
        <dl>
            <dd>{{ $t('Flood sensors') }}</dd>
            <dt>
                <div class="mb-3">
                    <div class="d-flex align-items-center bottom-border py-2" v-for="channel in sensors" :key="channel.id">
                        <div class="flex-grow-1">
                            <h5 class="my-1">
                                ID{{ channel.id }}
                                {{ channel.caption }}
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
                    :params="{skipIds: sensorsIds, deviceIds: channel.iodeviceId, fnc: 'FLOOD_SENSOR'}"/>
            </dt>
        </dl>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelsDropdown from "@/devices/channels-dropdown.vue";

    const props = defineProps({channel: Object});
    const emit = defineEmits('change');

    const channelsStore = useChannelsStore();

    const sensorsIds = computed(() => props.channel.config.sensorChannelIds || []);
    const sensors = computed({
        get() {
            return sensorsIds.value.map(id => channelsStore.all[id]);
        },
        set(value) {
            props.channel.config.sensorChannelIds = value.map(c => c.id);
            emit('change');
        }
    });

    function handleNewSensor(newSensor) {
        sensors.value = [...sensors.value, newSensor];
    }

    function handleRemoveSensor(sensorToRemove) {
        sensors.value = sensors.value.filter(ch => ch.id !== sensorToRemove.id);
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

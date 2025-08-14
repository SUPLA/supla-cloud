<template>

    <div>
        <SimpleDropdown v-model="localUILockMode" :options="['UNLOCKED', 'FULL', 'TEMPERATURE']">
            <template #button="{value}">
                {{ $t('Lock type') }}:
                {{ $t(`localUILock_${value}`) }}
            </template>
            <template #option="{option}">
                {{ $t(`localUILock_${option}`) }}
            </template>
        </SimpleDropdown>
        <div class="mt-3" v-if="newConfig.disabled === 'partial'">
            <label>{{ $t('Temperatures that can be set from local UI') }}</label>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <div>{{ $t('Minimum') }}</div>
                        <input type="number" class="form-control" step="0.1"
                            v-model="newConfig.minAllowedTemperatureSetpointFromLocalUI"
                            :max="newConfig.maxAllowedTemperatureSetpointFromLocalUI || maxUiTemperature"
                            :min="minUiTemperature" :placeholder="minUiTemperature">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <div>{{ $t('Maximum') }}</div>
                        <input type="number" class="form-control" step="0.1"
                            v-model="newConfig.maxAllowedTemperatureSetpointFromLocalUI"
                            :min="newConfig.minAllowedTemperatureSetpointFromLocalUI || minUiTemperature"
                            :max="maxUiTemperature" :placeholder="maxUiTemperature">
                    </div>
                </div>
            </div>
        </div>
        <SaveCancelChangesButtons :original="device.config.userInterface" :changes="newConfig"
            @cancel="cloneConfig()" @save="saveChanges()"/>
    </div>
</template>

<script setup>
    import {computed, ref} from "vue";
    import {deepCopy} from "@/common/utils";
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";
    import {devicesApi} from "@/api/devices-api";
    import {useDevicesStore} from "@/stores/devices-store";
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";

    const props = defineProps({device: Object});

    const newConfig = ref({});
    const cloneConfig = () => newConfig.value = deepCopy(props.device.config.userInterface);
    cloneConfig();

    const minUiTemperature = computed(() => props.device.config.userInterfaceConstraints?.minAllowedTemperatureSetpoint);
    const maxUiTemperature = computed(() => props.device.config.userInterfaceConstraints?.maxAllowedTemperatureSetpoint);

    const localUILockMode = computed({
        get() {
            if (newConfig.value.disabled === 'partial') {
                return 'TEMPERATURE';
            }
            return newConfig.value.disabled ? 'FULL' : 'UNLOCKED';
        },
        set(mode) {
            if (mode === 'FULL') {
                newConfig.value.disabled = true;
            } else if (mode === 'TEMPERATURE') {
                newConfig.value.disabled = 'partial';
            } else {
                newConfig.value.disabled = false;
            }
        }
    });

    async function saveChanges() {
        try {
            await devicesApi.update(props.device.id, {
                config: {userInterface: newConfig.value},
                configBefore: props.device.config,
            });
        } finally {
            await useDevicesStore().fetchDevice(props.device.id);
            cloneConfig();
        }
    }
</script>

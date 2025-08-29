<template>
    <div>
        <form @submit.prevent="saveChanges()">
            <DeviceDetailsTabBasicSettings :device="device" @change="onNewChanges($event)"/>

            <AccordionRoot>
                <AccordionItem v-for="cfg in tiles" :key="cfg.title" :title-i18n="cfg.title">
                    <Component :is="cfg.component" :device="device" @change="onNewChanges($event)"/>
                </AccordionItem>
            </AccordionRoot>

            <TransitionExpand>
                <div v-if="hasPendingChanges" class="text-center mt-3">
                    <FormButton button-class="btn-grey mx-1" @click="cancelChanges()" :disabled="loading">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel changes') }}
                    </FormButton>
                    <FormButton button-class="btn-white mx-1" submit :loading="loading">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save changes') }}
                    </FormButton>
                </div>
            </TransitionExpand>
        </form>

        <DependenciesWarningModal
            header-i18n="Some features depend on this device"
            deleting-header-i18n="Turning this device off will result in disabling features listed below."
            removing-header-i18n="Turning this device off will cause its channels not working in the following features."
            v-if="dependenciesThatWillBeDisabled"
            :dependencies="dependenciesThatWillBeDisabled"
            :loading="loading"
            @confirm="saveChanges(false)"
            @cancel="dependenciesThatWillBeDisabled = undefined"/>
    </div>
</template>

<script setup>
    import DeviceDetailsTabBasicSettings from "@/devices/details/device-details-tab-basic-settings.vue";
    import AccordionRoot from "@/common/gui/accordion/accordion-root.vue";
    import AccordionItem from "@/common/gui/accordion/accordion-item.vue";
    import DeviceDetailsTabSettingsLed from "@/devices/details/device-details-tab-settings-led.vue";
    import DeviceDetailsTabSettingsModbus from "@/devices/details/device-details-tab-settings-modbus.vue";
    import {computed, onMounted, ref} from "vue";
    import DeviceDetailsTabSettingsUserInterface from "@/devices/details/device-details-tab-settings-user-interface.vue";
    import {useDevicesStore} from "@/stores/devices-store";
    import {deepCopy} from "@/common/utils";
    import {devicesApi} from "@/api/devices-api";
    import {warningNotification} from "@/common/notifier";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import FormButton from "@/common/gui/FormButton.vue";
    import DeviceDetailsTabSettingsUpdates from "@/devices/details/device-details-tab-settings-updates.vue";
    import DeviceDetailsTabSettingsDatetime from "@/devices/details/device-details-tab-settings-datetime.vue";

    const props = defineProps({device: Object});

    const availableComponents = [
        {
            title: 'LED settings', // i18n
            component: DeviceDetailsTabSettingsLed,
            show: props.device.config?.powerStatusLed !== undefined || props.device.config?.statusLed !== undefined,
        },
        {
            title: 'Firmware updates', // i18n
            component: DeviceDetailsTabSettingsUpdates,
            show: props.device.config?.firmwareUpdatePolicy !== undefined,
        },
        {title: 'MODBUS', component: DeviceDetailsTabSettingsModbus, show: props.device.config?.modbus !== undefined}, // i18n
        {
            title: 'User interface', // i18n
            component: DeviceDetailsTabSettingsUserInterface,
            show: props.device.config?.userInterface !== undefined
                || props.device.config?.buttonVolume !== undefined
                || props.device.config?.homeScreen !== undefined
                || props.device.config?.homeScreenContentAvailable?.length > 0
        },
        {
            title: 'Device date and time', // i18n
            component: DeviceDetailsTabSettingsDatetime,
            show: props.device.config?.automaticTimeSync !== undefined
                || props.device.config?.firmwareUpdatePolicy !== undefined,
        }
    ];

    const tiles = computed(() => availableComponents.filter(t => t.show));

    const devicesStore = useDevicesStore();
    const editableConfig = ref({});
    const originalConfig = ref({});
    const dependenciesThatWillBeDisabled = ref(undefined);

    function onNewChanges(changes) {
        editableConfig.value = {...editableConfig.value, ...changes};
    }

    const cloneConfig = () => {
        dependenciesThatWillBeDisabled.value = undefined;
        editableConfig.value = deepCopy({
            comment: props.device.comment,
            enabled: props.device.enabled,
            ...props.device.config,
        })
        originalConfig.value = deepCopy(editableConfig.value);
    };

    const hasPendingChanges = computed(() => JSON.stringify(editableConfig.value) !== JSON.stringify(originalConfig.value));
    const loading = ref(false);

    onMounted(cloneConfig);

    async function saveChanges(safe = true) {
        loading.value = true;
        try {
            const request = {
                comment: editableConfig.value.comment,
                enabled: editableConfig.value.enabled,
                config: {...editableConfig.value},
                configBefore: originalConfig.value,
            };
            delete request.config.comment;
            delete request.config.enabled;
            delete request.configBefore.comment;
            delete request.configBefore.enabled;
            await devicesApi.update(props.device.id, request, safe);
            await devicesStore.fetchDevice(props.device.id);
            cloneConfig();
        } catch (error) {
            if (error.status === 409) {
                if (error.body?.dependencies) {
                    dependenciesThatWillBeDisabled.value = error.body;
                } else {
                    warningNotification(
                        'Settings have not been saved!', // i18n
                        'The configuration has been changed from another source (e.g. another browser tab, mobile app, device). Please adjust the settings and save again.' // i18n
                    )
                }
            }
        } finally {
            loading.value = false;
        }
    }

    async function cancelChanges() {
        cloneConfig();
        await devicesStore.fetchDevice(props.device.id);
        cloneConfig();
    }
</script>

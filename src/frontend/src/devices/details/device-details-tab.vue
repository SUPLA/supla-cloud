<template>
    <div class="container">
        <PendingChangesPage :is-pending="hasPendingChanges" @save="saveChanges()" @cancel="cancelChanges()" :loading="loading">
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <div class="details-page-block" v-for="tile in firstColumnTiles" :key="tile.title">
                        <h3 class="text-center">{{ $t(tile.title) }}</h3>
                        <Component :is="tile.component" :device="device" @change="onNewChanges($event)"/>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="details-page-block" v-for="tile in secondColumnTiles" :key="tile.title">
                        <h3 class="text-center">{{ $t(tile.title) }}</h3>
                        <Component :is="tile.component" :device="device" @change="onNewChanges($event)"/>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="details-page-block" v-for="tile in thirdColumnTiles" :key="tile.title">
                        <h3 class="text-center">{{ $t(tile.title) }}</h3>
                        <Component :is="tile.component" :device="device" @change="onNewChanges($event)"/>
                    </div>
                </div>
            </div>
        </PendingChangesPage>

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
    import DeviceDetailsTabFirmwareInfo from "@/devices/details/device-details-tab-firmware-info.vue";
    import DeviceDetailsTabLocation from "@/devices/details/device-details-tab-location.vue";
    import DeviceDetailsTabRemoteButtons from "@/devices/details/device-details-tab-remote-buttons.vue";
    import {computed, onMounted, ref} from "vue";
    import DeviceDetailsTabSettingsLed from "@/devices/details/device-details-tab-settings-led.vue";
    import DeviceDetailsTabSettingsModbus from "@/devices/details/device-details-tab-settings-modbus.vue";
    import DeviceDetailsTabSettingsOther from "@/devices/details/device-details-tab-settings-other.vue";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import {devicesApi} from "@/api/devices-api";
    import {warningNotification} from "@/common/notifier";
    import {useDevicesStore} from "@/stores/devices-store";
    import {deepCopy} from "@/common/utils";
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import DeviceDetailsTabSettingsScreenBrightness from "@/devices/details/device-details-tab-settings-screen-brightness.vue";
    import DeviceDetailsTabSettingsUserInterface from "@/devices/details/device-details-tab-settings-user-interface.vue";
    import DeviceDetailsTabSettingsHomeScreen from "@/devices/details/device-details-tab-settings-home-screen.vue";

    const props = defineProps({device: Object});

    const availableTiles = [
        {title: 'Device information', component: DeviceDetailsTabFirmwareInfo, show: !props.device.isVirtual}, // i18n
        {title: 'Default location', component: DeviceDetailsTabLocation, show: !props.device.locked}, // i18n
        {title: 'Basic settings', component: DeviceDetailsTabBasicSettings, show: true}, // i18n
        {
            title: 'LED settings', // i18n
            component: DeviceDetailsTabSettingsLed,
            show: props.device.config?.powerStatusLed !== undefined || props.device.config?.statusLed !== undefined,
        },
        {
            title: 'Remote access', // i18n
            component: DeviceDetailsTabRemoteButtons,
            show: !props.device.locked && (
                props.device.flags.enterConfigurationModeAvailable
                || props.device.flags.identifyDeviceAvailable
                || props.device.remoteRestartAvailable
                || props.device.flags.factoryResetSupported
                || props.device.flags.setCfgModePasswordSupported
                || props.device.flags.pairingSubdevicesAvailable
            )
        },
        {title: 'MODBUS', component: DeviceDetailsTabSettingsModbus, show: props.device.config?.modbus !== undefined}, // i18n
        {title: 'Home screen', component: DeviceDetailsTabSettingsHomeScreen, show: props.device.config?.homeScreen !== undefined}, // i18n
        {
            title: 'User interface', // i18n
            component: DeviceDetailsTabSettingsUserInterface,
            show: props.device.config?.userInterface !== undefined || props.device.config?.buttonVolume !== undefined
        },
        {
            title: 'Screen brightness', // i18n
            component: DeviceDetailsTabSettingsScreenBrightness,
            show: props.device.config?.screenBrightness !== undefined
        },
        {
            title: 'Other settings', // i18n
            component: DeviceDetailsTabSettingsOther,
            show: props.device.config?.automaticTimeSync !== undefined
                || props.device.config?.firmwareUpdatePolicy !== undefined,
        }
    ];

    const tiles = computed(() => availableTiles.filter(t => t.show));
    const firstColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 0));
    const secondColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 1));
    const thirdColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 2));

    const devicesStore = useDevicesStore();
    const newConfig = ref({});
    const originalConfig = ref({});
    const dependenciesThatWillBeDisabled = ref(undefined);

    function onNewChanges(changes) {
        newConfig.value = {...newConfig.value, ...changes};
    }

    const cloneConfig = () => {
        dependenciesThatWillBeDisabled.value = undefined;
        newConfig.value = deepCopy({
            comment: props.device.comment,
            enabled: props.device.enabled,
            ...props.device.config,
        })
        originalConfig.value = deepCopy(newConfig.value);
    };

    const hasPendingChanges = computed(() => JSON.stringify(newConfig.value) !== JSON.stringify(originalConfig.value));
    const loading = ref(false);

    onMounted(cloneConfig);

    async function saveChanges(safe = true) {
        loading.value = true;
        try {
            const request = {
                comment: newConfig.value.comment,
                enabled: newConfig.value.enabled,
                config: {...newConfig.value},
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
        await devicesStore.fetchDevice(props.device.id);
        cloneConfig();
    }
</script>

<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <form class="details-page-block" @submit.prevent="saveChanges()">
                    <h3 class="text-center">{{ $t('Device information') }}</h3>
                    <dl v-if="!device.isVirtual">
                        <dd>GUID</dd>
                        <dt>{{ device.gUIDString }}</dt>
                        <dt>{{ $t('SoftVer') }}</dt>
                        <dd>{{ device.softwareVersion }}</dd>
                        <dt>{{ $t('Registered') }}</dt>
                        <dd>
                            {{ formatDateTime(device.regDate) }}
                            <DateTimeRelativeLabel :datetime="device.regDate" pattern="(%s)" class="small text-muted"/>
                        </dd>
                        <dt>{{ $t('Last connection') }}</dt>
                        <dd>
                            {{ formatDateTime(device.lastConnected) }}
                            <DateTimeRelativeLabel :datetime="device.lastConnected" pattern="(%s)" class="small text-muted"/>
                        </dd>
                    </dl>
                    <dl>
                        <dt>{{ $t('Name') }}</dt>
                        <dd>
                            <input type="text" :disabled="loading" class="form-control" v-model="changes.comment">
                        </dd>
                    </dl>
                    <dl v-if="!device.locked && !device.isVirtual">
                        <dt class="mb-2">{{ $t('Enabled') }}</dt>
                        <dd>
                            <toggler v-model="changes.enabled" :disabled="loading"/>
                        </dd>
                    </dl>
                    <transition-expand>
                        <div class="text-center mt-3" v-if="hasPendingChanges">
                            <a class="btn btn-grey mx-1"
                                @click="cancelChanges()">
                                <i class="pe-7s-back"></i>
                                {{ $t('Cancel changes') }}
                            </a>
                            <button class="btn btn-white mx-1" type="submit" :disabled="loading">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save changes') }}
                            </button>
                        </div>
                    </transition-expand>
                </form>
            </div>
            <div class="col-md-4">
                <div class="details-page-block">
                    <h3 class="text-center">{{ $t('Location') }}</h3>
                    <router-link :to="{name: 'location', params: {id: device.originalLocationId}}"
                        class="original-location"
                        v-if="device.originalLocationId && device.originalLocationId !== device.locationId">
                        {{ $t('Original location') }}
                        <strong>{{ originalLocation.caption }}</strong>
                    </router-link>
                    <SquareLocationChooser v-model="location" @chosen="changeLocation($event)"/>
                </div>
                <div class="details-page-block" v-if="!device.locked && device.flags.automaticFirmwareUpdatesSupported">
                    <h3 class="text-center">{{ $t('Firmware updates') }}</h3>
                    <DeviceOtaUpdatesButtons :device="device"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="details-page-block">
                    <h3 class="text-center">{{ $t('Access ID') }}</h3>
                    <div class="list-group" v-if="accessIds.length > 0">
                        <router-link :to="{name: 'accessId', params: {id: aid.id}}"
                            v-for="aid in accessIds"
                            class="list-group-item"
                            :key="aid.id">
                            ID{{ aid.id }} {{ aid.caption }}
                        </router-link>
                    </div>
                    <div class="list-group" v-else>
                        <div class="list-group-item">
                            <em>{{ $t('None') }}</em>
                        </div>
                    </div>
                </div>
                <div class="details-page-block" v-if="!device.locked">
                    <h3 class="text-center">{{ $t('Remote access') }}</h3>
                    <DeviceEnterConfigurationModeButton :device="device" class="mb-2"/>
                    <DeviceIdentifyDeviceButton :device="device" class="mb-2"/>
                    <DeviceRemoteRestartButton :device="device" class="mb-2"/>
                    <DeviceRemoteFactoryResetButton :device="device" class="mb-2"/>
                    <DeviceSetTimeButton :device="device" class="mb-2"/>
                    <DeviceSetCfgPasswordButton :device="device" class="mb-2"/>
                    <DevicePairSubdeviceButton :device="device" class="mb-2"/>
                </div>
            </div>
        </div>
        <DependenciesWarningModal
            header-i18n="Some features depend on this device"
            deleting-header-i18n="Turning this device off will result in disabling features listed below."
            removing-header-i18n="Turning this device off will cause its channels not working in the following features."
            v-if="dependenciesThatWillBeDisabled"
            :dependencies="dependenciesThatWillBeDisabled"
            :loading="loading"
            @confirm="saveChanges(false)"
            @cancel="dependenciesThatWillBeDisabled = undefined"/>
        <DependenciesWarningModal
            header-i18n="Are you sure you want to change device’s location?"
            description-i18n="Changing the location will also imply changing the location of the following items."
            deleting-header-i18n=""
            removing-header-i18n=""
            :loading="loading"
            v-if="dependenciesThatWillChangeLocation"
            :dependencies="dependenciesThatWillChangeLocation"
            @cancel="loading = dependenciesThatWillChangeLocation = undefined"
            @confirm="changeLocation(dependenciesThatWillChangeLocation.newLocation, false)"/>
    </div>
</template>

<script setup>
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {formatDateTime} from "@/common/filters-date";
    import DateTimeRelativeLabel from "@/common/date-time-relative-label.vue";
    import {computed, onMounted, reactive, ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {devicesApi} from "@/api/devices-api";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useLocationsStore} from "@/stores/locations-store";
    import SquareLocationChooser from "@/locations/square-location-chooser.vue";
    import {useAccessIdsStore} from "@/stores/access-ids-store";
    import DeviceEnterConfigurationModeButton from "@/devices/details/device-enter-configuration-mode-button.vue";
    import DeviceIdentifyDeviceButton from "@/devices/details/device-identify-device-button.vue";
    import DeviceRemoteRestartButton from "@/devices/details/device-remote-restart-button.vue";
    import DeviceOtaUpdatesButtons from "@/devices/details/device-ota-updates-buttons.vue";
    import DeviceRemoteFactoryResetButton from "@/devices/details/device-remote-factory-reset-button.vue";
    import DeviceSetTimeButton from "@/devices/details/device-set-time-button.vue";
    import DeviceSetCfgPasswordButton from "@/devices/details/device-set-cfg-password-button.vue";
    import DevicePairSubdeviceButton from "@/devices/details/device-pair-subdevice-button.vue";

    const props = defineProps({device: Object});

    const changes = reactive({
        comment: props.device.comment,
        enabled: props.device.enabled,
    });

    const locationsStore = useLocationsStore();
    const accessIdsStore = useAccessIdsStore();
    const location = computed(() => locationsStore.all[props.device.locationId] || undefined);
    const originalLocation = computed(() => locationsStore.all[props.device.originalLocationId] || undefined);
    const accessIds = computed(() => accessIdsStore.list.filter((aid) => aid.locations.map((l) => l.id).includes(props.device.locationId)));

    const loading = ref(false);
    const dependenciesThatWillBeDisabled = ref(undefined);
    const dependenciesThatWillChangeLocation = ref(undefined);
    const hasPendingChanges = computed(() => changes.comment !== props.device.comment || changes.enabled !== props.device.enabled);

    function cancelChanges() {
        changes.comment = props.device.comment;
        changes.enabled = props.device.enabled;
    }

    onMounted(cancelChanges);

    async function saveChanges(safe = true) {
        loading.value = true;
        dependenciesThatWillBeDisabled.value = undefined;
        try {
            await devicesApi.update(props.device.id, changes, safe);
            await useChannelsStore().refetchAll();
            cancelChanges();
        } catch (error) {
            if (error.status === 409) {
                dependenciesThatWillBeDisabled.value = error.body;
            }
        } finally {
            loading.value = false;
        }
    }

    async function changeLocation(location, safe = true) {
        loading.value = true;
        dependenciesThatWillChangeLocation.value = undefined;
        try {
            await devicesApi.update(props.device.id, {locationId: location.id}, safe);
            await useChannelsStore().refetchAll();
        } catch (error) {
            if (error.status === 409) {
                dependenciesThatWillChangeLocation.value = error.body;
                dependenciesThatWillChangeLocation.value.newLocation = location;
            }
        } finally {
            loading.value = false;
        }
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .original-location {
        display: block;
        padding: 5px;
        background: rgba(193, 209, 81, 0.75);
        color: $supla-white;
        margin-bottom: 10px;
        strong {
            display: block;
        }
    }
</style>

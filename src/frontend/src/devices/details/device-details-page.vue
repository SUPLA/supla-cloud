<template>
    <page-container :error="error">
        <loading-cover :loading="!device || loading">
            <div :class="['bg-green form-group', {'bg-red': device.locked}]"
                v-if="device">
                <div class="container">
                    <pending-changes-page :header="deviceTitle"
                        @cancel="cancelChanges()"
                        @save="saveChanges()"
                        @delete="deleteConfirm = true"
                        :deletable="true"
                        :is-pending="hasPendingChanges">
                        <div class="row text-center">
                            <div class="col-sm-4">
                                <h3>{{ $t('Device') }}</h3>
                                <div class="text-center form-group" v-if="!device.locked">
                                    <ConnectionStatusLabel :model="deviceFromStore"/>
                                </div>
                                <div class="hover-editable text-left">
                                    <dl>
                                        <dd>{{ $t('Name') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="updateDevice()"
                                                v-model="device.comment">
                                        </dt>
                                        <dd>GUID</dd>
                                        <dt>{{ device.gUIDString }}</dt>
                                        <dd>{{ $t('SoftVer') }}</dd>
                                        <dt>{{ device.softwareVersion }}</dt>
                                        <dd>{{ $t('Registered') }}</dd>
                                        <dt>{{ device.regDate | formatDateTime }}</dt>
                                        <dd>{{ $t('Last connection') }}</dd>
                                        <dt>{{ device.lastConnected | formatDateTime }}</dt>
                                    </dl>
                                    <dl v-if="!device.locked">
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt>
                                            <toggler v-model="device.enabled"
                                                true-color="white"
                                                @input="updateDevice()"></toggler>
                                        </dt>
                                    </dl>
                                </div>
                                <div v-if="!device.locked">
                                    <DeviceEnterConfigurationModeButton :device="device"/>
                                    <DeviceIdentifyDeviceButton :device="device"/>
                                    <DeviceRemoteRestartButton :device="device"/>
                                    <DeviceSetTimeButton :device="device"/>
                                    <DevicePairSubdeviceButton :device="device"/>
                                </div>
                            </div>
                            <div class="col-sm-4" v-if="!device.locked">
                                <h3>{{ $t('Location') }}</h3>
                                <router-link :to="{name: 'location', params: {id: device.originalLocationId}}"
                                    class="original-location"
                                    v-if="device.originalLocationId && device.originalLocationId != device.locationId">
                                    {{ $t('Original location') }}
                                    <strong>{{ originalLocation.caption }}</strong>
                                </router-link>
                                <SquareLocationChooser v-model="device.location" @chosen="changeLocation($event)"/>
                            </div>
                            <div class="col-sm-4" v-if="!device.locked">
                                <h3>{{ $t('Access ID') }}</h3>
                                <div class="list-group"
                                    v-if="device.location.relationsCount.accessIds > 0 && device.location.accessIds">
                                    <router-link :to="{name: 'accessId', params: {id: aid.id}}"
                                        v-for="aid in device.location.accessIds"
                                        class="list-group-item"
                                        :key="aid.id">
                                        ID{{ aid.id }} {{ aid.caption }}
                                    </router-link>
                                </div>
                                <div class="list-group"
                                    v-else>
                                    <div class="list-group-item"
                                        v-if="device.location.relationsCount.accessIds">
                                        <em>{{ $t('Save changes to see Access IDs') }}</em>
                                    </div>
                                    <div class="list-group-item"
                                        v-else>
                                        <em>{{ $t('None') }}</em>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
            </div>
        </loading-cover>

        <DeviceDetailsTabs :device="device" v-if="device"/>

        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteDevice()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure?')"
            :loading="loading">
            <p>{{ $t('Confirm if you want to remove {deviceName} device and all of its channels.', {deviceName: device.name}) }}</p>
        </modal-confirm>
        <dependencies-warning-modal
            header-i18n="Some features depend on this device"
            deleting-header-i18n="Turning this device off will result in disabling features listed below."
            removing-header-i18n="Turning this device off will cause its channels not working in the following features."
            v-if="dependenciesThatWillBeDisabled"
            :dependencies="dependenciesThatWillBeDisabled"
            :loading="loading"
            @confirm="saveChanges(false)"
            @cancel="dependenciesThatWillBeDisabled = undefined"></dependencies-warning-modal>
        <dependencies-warning-modal
            header-i18n="Some features depend on this device"
            description-i18n="Some of the features you have configured depend on channels from this device."
            deleting-header-i18n="The following items will be deleted with this device:"
            removing-header-i18n="The following items use the channels of these device. These references will be also removed."
            :loading="loading"
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteDevice(false)"
            @cancel="dependenciesThatPreventsDeletion = undefined"></dependencies-warning-modal>
        <dependencies-warning-modal
            header-i18n="Are you sure you want to change device’s location?"
            description-i18n="Changing the location will also imply changing the location of the following items."
            deleting-header-i18n=""
            removing-header-i18n=""
            :loading="loading"
            v-if="dependenciesThatWillChangeLocation"
            :dependencies="dependenciesThatWillChangeLocation"
            @cancel="loading = dependenciesThatWillChangeLocation = undefined"
            @confirm="changeLocation(dependenciesThatWillChangeLocation.newLocation, false)"></dependencies-warning-modal>
    </page-container>
</template>

<script>
    import {deviceTitle} from "../../common/filters";
    import throttle from "lodash/throttle";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import ConnectionStatusLabel from "../list/connection-status-label";
    import SquareLocationChooser from "../../locations/square-location-chooser";
    import PageContainer from "../../common/pages/page-container";
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal";
    import DeviceEnterConfigurationModeButton from "./device-enter-configuration-mode-button";
    import DeviceDetailsTabs from "@/devices/details/device-details-tabs.vue";
    import {extendObject} from "@/common/utils";
    import DeviceSetTimeButton from "@/devices/details/device-set-time-button.vue";
    import DevicePairSubdeviceButton from "@/devices/details/device-pair-subdevice-button.vue";
    import DeviceRemoteRestartButton from "@/devices/details/device-remote-restart-button.vue";
    import DeviceIdentifyDeviceButton from "@/devices/details/device-identify-device-button.vue";
    import {mapStores} from "pinia";
    import {useDevicesStore} from "@/stores/devices-store";
    import {useLocationsStore} from "@/stores/locations-store";
    import {useChannelsStore} from "@/stores/channels-store";

    export default {
        props: ['id'],
        components: {
            DeviceIdentifyDeviceButton,
            DeviceRemoteRestartButton,
            DevicePairSubdeviceButton,
            DeviceSetTimeButton,
            DeviceDetailsTabs,
            DeviceEnterConfigurationModeButton,
            DependenciesWarningModal,
            PageContainer,
            ConnectionStatusLabel,
            PendingChangesPage,
            Toggler,
            SquareLocationChooser,
        },
        data() {
            return {
                device: undefined,
                error: false,
                loading: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                dependenciesThatWillBeDisabled: undefined,
                dependenciesThatPreventsDeletion: undefined,
                dependenciesThatWillChangeLocation: undefined,
            };
        },
        mounted() {
            this.fetchDevice();
        },
        methods: {
            fetchDevice() {
                this.loading = true;
                this.error = false;
                return this.$http.get(`iodevices/${this.id}?include=location,accessids`, {skipErrorHandler: [403, 404]})
                    .then(response => {
                        this.device = response.body;
                        this.loading = false;
                        this.hasPendingChanges = false;
                        this.$set(this.device, 'hasPendingChanges', false);
                    })
                    .catch(response => this.error = response.status);
            },
            updateDevice() {
                this.hasPendingChanges = true;
                this.$set(this.device, 'hasPendingChanges', true);
            },
            cancelChanges() {
                this.fetchDevice();
            },
            saveChanges: throttle(function (safe = true) {
                this.loading = true;
                this.dependenciesThatWillBeDisabled = undefined;
                this.$http.put(`iodevices/${this.id}` + (safe ? '?safe=1' : ''), this.device, {skipErrorHandler: [409]})
                    .then(response => extendObject(this.device, response.body))
                    .then(() => this.hasPendingChanges = false)
                    .then(() => this.$set(this.device, 'hasPendingChanges', false))
                    .then(() => {
                        if (!this.device.location.accessIds) {
                            return this.fetchDevice();
                        }
                    })
                    .then(() => this.channelsStore.refetchAll())
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.dependenciesThatWillBeDisabled = body;
                        }
                    })
                    .finally(() => this.loading = false);
            }, 1000),
            deleteDevice(safe = true) {
                this.loading = true;
                this.devicesStore.remove(this.id, safe)
                    .then(() => this.$router.push({name: 'me'}))
                    .then(() => this.channelsStore.refetchAll())
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);
            },
            changeLocation(location, safe = true) {
                this.loading = true;
                return this.$http.put(`iodevices/${this.id}${safe ? '?safe=1' : ''}`, {locationId: location.id}, {skipErrorHandler: [409]})
                    .then(() => {
                        this.dependenciesThatWillChangeLocation = undefined;
                        this.$set(this.device, 'location', location);
                    })
                    .then(() => this.channelsStore.refetchAll())
                    .catch(response => {
                        if (response.status === 409) {
                            this.dependenciesThatWillChangeLocation = response.body;
                            this.dependenciesThatWillChangeLocation.newLocation = location;
                        }
                    })
                    .finally(() => this.loading = false);
            },
        },
        computed: {
            deviceTitle() {
                return deviceTitle(this.device, this);
            },
            ...mapStores(useDevicesStore, useLocationsStore, useChannelsStore),
            originalLocation() {
                return this.locationsStore.all[this.device.originalLocationId] || undefined;
            },
            deviceFromStore() { // TODO temporary, the whole view should use it
                return this.devicesStore.all[this.id];
            }
        }
    };
</script>

<style lang="scss">
    @import '../../styles/variables';

    .bg-green {
        margin-top: -21px;
        padding: 20px 0 10px;
        color: $supla-white;
        background-color: $supla-green;
        &.bg-red {
            background-color: $supla-grey-dark !important;
        }
        .square-link {
            border-color: $supla-black;
        }
        .list-group .list-group-item {
            background: transparent;
            color: $supla-white;
            border-color: $supla-white;
            &:hover {
                background: rgba($supla-white, .1);
            }
        }
        .hover-editable {
            .form-control {
                color: $supla-white !important;
                background: transparent !important;
            }
            &:hover {
                .form-control {
                    border: 1px solid $supla-white;
                }
            }
        }
    }

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

<template>
    <page-container :error="error">
        <loading-cover :loading="!device || loading">
            <div class="bg-green form-group"
                v-if="device">
                <div class="container">
                    <pending-changes-page :header="deviceTitle"
                        @cancel="cancelChanges()"
                        @save="saveChanges()"
                        @delete="deleteConfirm = true"
                        deletable="true"
                        :is-pending="hasPendingChanges">
                        <div class="row hidden-xs">
                            <div class="col-xs-12">
                                <dots-route :dot1-color="device.connected === false ? 'red' : 'green'"
                                    :dot3-color="device.location.relationsCount.accessIds > 0 ? 'green' : 'red'"></dots-route>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-sm-4">
                                <h3>{{ $t('Device') }}</h3>
                                <div class="text-center form-group">
                                    <device-connection-status-label :device="device"></device-connection-status-label>
                                </div>
                                <div class="hover-editable text-left">
                                    <dl>
                                        <dd>GUID</dd>
                                        <dt>{{ this.device.gUIDString }}</dt>
                                        <dd>{{ $t('Registered') }}</dd>
                                        <dt>{{ this.device.regDate | moment("LT L")}}</dt>
                                        <dd>{{ $t('Last connection') }}</dd>
                                        <dt>{{ this.device.lastConnected | moment("LT L")}}</dt>
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt>
                                            <toggler v-model="device.enabled"
                                                true-color="white"
                                                @input="updateDevice()"></toggler>
                                        </dt>
                                        <dd>{{ $t('Comment') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="updateDevice()"
                                                v-model="device.comment">
                                        </dt>
                                    </dl>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3>{{ $t('Location') }}</h3>
                                <router-link :to="{name: 'location', params: {id: device.originalLocationId}}"
                                    class="original-location"
                                    v-if="device.originalLocationId && device.originalLocationId != device.locationId">
                                    {{ $t('Original location') }}
                                    <strong>{{ device.originalLocation.caption }}</strong>
                                </router-link>
                                <square-location-chooser v-model="device.location"
                                    @input="onLocationChange($event)"></square-location-chooser>
                            </div>
                            <div class="col-sm-4">
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
        <channel-list-page :device-id="id"
            v-if="device"></channel-list-page>
        <disable-io-with-dependencies-modal message-i18n="Turning this device off will result in disabling all the associated schedules."
            v-if="dependenciesThatWillBeDisabled"
            :dependencies="dependenciesThatWillBeDisabled"
            @confirm="saveChanges(false)"
            @cancel="dependenciesThatWillBeDisabled = undefined"></disable-io-with-dependencies-modal>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteDevice()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure?')"
            :loading="loading">
            <p>{{ $t('Confirm if you want to remove {deviceName} device and all of its channels.', {deviceName: device.name}) }}</p>
        </modal-confirm>
        <delete-io-with-dependencies-modal v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteDevice(false)"
            @cancel="dependenciesThatPreventsDeletion = undefined"></delete-io-with-dependencies-modal>
    </page-container>
</template>

<script>
    import {deviceTitle} from "../../common/filters";
    import DotsRoute from "../../common/gui/dots-route.vue";
    import throttle from "lodash/throttle";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import ChannelListPage from "../../channels/channel-list-page";
    import DeviceConnectionStatusLabel from "../list/device-connection-status-label";
    import SquareLocationChooser from "../../locations/square-location-chooser";
    import PageContainer from "../../common/pages/page-container";
    import DeleteIoWithDependenciesModal from "./delete-io-with-dependencies-modal";
    import DisableIoWithDependenciesModal from "./disable-io-with-dependencies-modal";

    export default {
        props: ['id'],
        components: {
            DisableIoWithDependenciesModal,
            DeleteIoWithDependenciesModal,
            PageContainer,
            DeviceConnectionStatusLabel,
            ChannelListPage,
            PendingChangesPage,
            DotsRoute,
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
                dependenciesThatPreventsDeletion: undefined
            };
        },
        mounted() {
            this.fetchDevice();
        },
        methods: {
            fetchDevice() {
                this.loading = true;
                this.error = false;
                return this.$http.get(`iodevices/${this.id}?include=location,originalLocation,accessids`, {skipErrorHandler: [403, 404]})
                    .then(response => {
                        this.device = response.body;
                        this.loading = false;
                        this.hasPendingChanges = false;
                    })
                    .catch(response => this.error = response.status);
            },
            updateDevice() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.fetchDevice();
            },
            saveChanges: throttle(function (safe = true) {
                this.loading = true;
                this.dependenciesThatWillBeDisabled = undefined;
                this.$http.put(`iodevices/${this.id}` + (safe ? '?safe=1' : ''), this.device, {skipErrorHandler: true})
                    .then(response => $.extend(this.device, response.body))
                    .then(() => this.hasPendingChanges = false)
                    .then(() => {
                        if (!this.device.location.accessIds) {
                            return this.fetchDevice();
                        }
                    })
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.dependenciesThatWillBeDisabled = body;
                        }
                    })
                    .finally(() => this.loading = false);
            }, 1000),
            deleteDevice(safe = true) {
                this.loading = true;
                this.$http.delete(`iodevices/${this.id}?safe=${safe ? '1' : '0'}`, {skipErrorHandler: [409]})
                    .then(() => this.$router.push({name: 'me'}))
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);
            },
            onLocationChange(location) {
                this.$set(this.device, 'location', location);
                this.updateDevice();
            }
        },
        computed: {
            deviceTitle() {
                return deviceTitle(this.device, this);
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

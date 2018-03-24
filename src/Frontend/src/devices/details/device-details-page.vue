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
                                    :dot3-color="device.location.accessIdsIds.length > 0 ? 'green' : 'red'"></dots-route>
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
                                        <dd>{{ $t('GUID') }}</dd>
                                        <dt>{{ this.device.gUIDString }}</dt>
                                        <dd>{{ $t('Registered') }}</dd>
                                        <dt>{{ this.device.regdate | moment("LT L")}}</dt>
                                        <dd>{{ $t('Last connection') }}</dd>
                                        <dt>{{ this.device.lastconnected | moment("LT L")}}</dt>
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
                                                @change="updateDevice()"
                                                v-model="device.comment">
                                        </dt>
                                    </dl>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3>{{ $t('Location') }}</h3>
                                <router-link :to="{name: 'location', params: device.originalLocation}"
                                    class="original-location"
                                    v-if="device.originalLocationId && device.originalLocationId != device.locationId">
                                    {{ $t('Original location')}}
                                    <strong>{{ device.originalLocation.caption }}</strong>
                                </router-link>
                                <square-location-chooser v-model="device.location"
                                    @input="onLocationChange($event)"></square-location-chooser>
                            </div>
                            <div class="col-sm-4">
                                <h3>{{ $t('Access ID') }}</h3>
                                <div class="list-group"
                                    v-if="device.location.accessIdsIds.length > 0 && device.location.accessIds">
                                    <router-link :to="{name: 'accessId', params: aid}"
                                        v-for="aid in device.location.accessIds"
                                        class="list-group-item"
                                        :key="aid.id">
                                        ID{{ aid.id }} {{ aid.caption }}
                                    </router-link>
                                </div>
                                <div class="list-group"
                                    v-else>
                                    <div class="list-group-item"
                                        v-if="device.location.accessIdsIds.length">
                                        <em>{{ $t('Save changes to see the Access IDs') }}</em>
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
        <disabling-schedules-modal message="Turning this device off will result in disabling all the associated schedules."
            v-if="showSchedulesDisablingConfirmation"
            :schedules="schedules"
            @confirm="saveChanges(true)"
            @cancel="showSchedulesDisablingConfirmation = false"></disabling-schedules-modal>
        <enabling-schedules-modal v-if="showSchedulesEnablingConfirmation"
            :schedules="schedules"
            @confirm="showSchedulesEnablingConfirmation = false"
            @cancel="showSchedulesEnablingConfirmation = false"></enabling-schedules-modal>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteDevice()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure?')"
            :loading="loading">
            <p>{{ $t('Confirm if you want to remove {deviceName} device', {deviceName: device.name}) }}</p>
        </modal-confirm>
    </page-container>
</template>

<script>
    import {deviceTitle, withBaseUrl} from "../../common/filters";
    import DotsRoute from "../../common/gui/dots-route.vue";
    import throttle from "lodash/throttle";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import ChannelListPage from "../../channels/channel-list-page";
    import DeviceConnectionStatusLabel from "../list/device-connection-status-label";
    import DisablingSchedulesModal from "../../schedules/modals/disabling-schedules-modal";
    import EnablingSchedulesModal from "../../schedules/modals/enabling-schedules-modal";
    import SquareLocationChooser from "../../locations/square-location-chooser";
    import PageContainer from "../../common/pages/page-container";

    export default {
        props: ['id'],
        components: {
            PageContainer,
            EnablingSchedulesModal,
            DisablingSchedulesModal,
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
                showSchedulesDisablingConfirmation: false,
                showSchedulesEnablingConfirmation: false,
                schedules: undefined
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
            saveChanges: throttle(function (confirm = false) {
                this.loading = true;
                this.showSchedulesDisablingConfirmation = this.showSchedulesEnablingConfirmation = false;
                this.$http.put(`iodevices/${this.id}` + (confirm ? '?confirm=1' : ''), this.device, {skipErrorHandler: true})
                    .then(response => $.extend(this.device, response.body))
                    .then(() => this.hasPendingChanges = false)
                    .then(() => {
                        if (!this.device.location.accessIds) {
                            return this.fetchDevice();
                        }
                    })
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.schedules = body.schedules.filter(schedule => schedule.enabled);
                            this.showSchedulesDisablingConfirmation = true;
                        }
                    })
                    .finally(() => this.loading = false);
            }, 1000),
            deleteDevice() {
                this.loading = true;
                this.$http.delete(`iodevices/${this.id}`).then(() => window.location.assign(withBaseUrl('me')));
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
                color: $supla-white;
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

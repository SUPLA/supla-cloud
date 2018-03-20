<template>
    <div>
        <loading-cover :loading="!device || loading">
            <div class="bg-green form-group">
                <div class="container"
                    v-if="device">
                    <pending-changes-page :header="deviceTitle"
                        @cancel="cancelChanges()"
                        @save="saveChanges()"
                        @delete="deleteConfirm = true"
                        deletable="true"
                        :is-pending="hasPendingChanges">
                        <div class="row hidden-xs">
                            <div class="col-xs-12">
                                <dots-route></dots-route>
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
                                        <dd>{{ $t('Registred') }}</dd>
                                        <dt>{{ this.device.regdate | moment("LT L")}}</dt>
                                        <dd>{{ $t('Last connection') }}</dd>
                                        <dt>{{ this.device.lastconnected | moment("LT L")}}</dt>
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt class="text-center">
                                            <toggler v-model="device.enabled"
                                                @input="updateDevice()"></toggler>
                                        </dt>
                                        <dd>{{ $t('Comment') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control text-center"
                                                @change="updateDevice()"
                                                v-model="device.comment">
                                        </dt>
                                        <!--<dd>{{ $t('Show in clients') }}</dd>-->
                                        <!--<dt class="text-center">-->
                                        <!--<toggler v-model="channel.hidden"-->
                                        <!--invert="true"-->
                                        <!--@input="updateChannel()"></toggler>-->
                                        <!--</dt>-->
                                    </dl>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3>{{ $t('Location') }}</h3>

                            </div>
                            <div class="col-sm-4">
                                <h3>{{ $t('Access ID') }}</h3>

                            </div>
                        </div>
                    </pending-changes-page>
                </div>
            </div>
            <disabling-schedules-modal message="Turning this device off will result in disabling all the associated schedules."
                v-if="showSchedulesDisablingConfirmation"
                :schedules="schedules"
                @confirm="saveChanges(true)"
                @cancel="showSchedulesDisablingConfirmation = false"></disabling-schedules-modal>
            <enabling-schedules-modal v-if="showSchedulesEnablingConfirmation"
                :schedules="schedules"
                @confirm="showSchedulesEnablingConfirmation = false"
                @cancel="showSchedulesEnablingConfirmation = false"></enabling-schedules-modal>
            <channel-list-page :device-id="deviceId"></channel-list-page>
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteDevice()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure?')"
                :loading="loading">
                <p>{{ $t('Confirm if you want to remove {deviceName} device', {deviceName: device.name}) }}</p>
            </modal-confirm>
        </loading-cover>
    </div>
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

    export default {
        props: ['deviceId'],
        components: {
            EnablingSchedulesModal,
            DisablingSchedulesModal,
            DeviceConnectionStatusLabel,
            ChannelListPage,
            PendingChangesPage,
            DotsRoute,
            Toggler,
        },
        data() {
            return {
                device: undefined,
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
                this.$http.get(`iodevices/${this.deviceId}?include=location`).then(response => {
                    this.device = response.body;
                    this.loading = false;
                    this.hasPendingChanges = false;
                });
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
                this.$http.put(`iodevices/${this.deviceId}` + (confirm ? '?confirm=1' : ''), this.device, {skipErrorHandler: true})
                    .then(response => $.extend(this.device, response.body))
                    .then(() => this.hasPendingChanges = false)
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
                this.$http.delete(`iodevices/${this.deviceId}`).then(() => window.location.assign(withBaseUrl('me')));
            },
            onLocationChange(location) {
                this.channel.inheritedLocation = !location;
                if (!location) {
                    location = this.channel.iodevice.location;
                }
                this.$set(this.channel, 'location', location);
                this.updateChannel();
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
    }
</style>

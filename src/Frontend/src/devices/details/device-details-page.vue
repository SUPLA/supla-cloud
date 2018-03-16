<template>
    <div>
        <loading-cover :loading="!device || loading">
            <div class="bg-green">
                <div class="container"
                    v-if="device">
                    <pending-changes-page :header="deviceTitle"
                        @cancel="cancelChanges()"
                        @save="saveChanges()"
                        :is-pending="hasPendingChanges">
                        <div class="row hidden-xs">
                            <div class="col-xs-12">
                                <dots-route></dots-route>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-sm-4">
                                <h3>{{ $t('Device') }}</h3>
                                <div class="hover-editable text-left">
                                    <dl>
                                        <dd>{{ $t('GUID') }}</dd>
                                        <dt>{{ this.device.gUIDString }}</dt>
                                        <dd>{{ $t('Registred') }}</dd>
                                        <dt>{{ this.device.regdate | moment("LT L")}}</dt>
                                        <dd>{{ $t('Last connection') }}</dd>
                                        <dt>{{ this.device.lastconnected | moment("LT L")}}</dt>
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
                                    <channel-params-form :channel="channel"
                                        @change="updateChannel()"></channel-params-form>
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
        </loading-cover>
    </div>
</template>

<script>
    import {deviceTitle} from "../../common/filters";
    import DotsRoute from "../../common/gui/dots-route.vue";
    import throttle from "lodash/throttle";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";

    export default {
        props: ['deviceId'],
        components: {
            PendingChangesPage,
            DotsRoute,
            Toggler,
        },
        data() {
            return {
                device: undefined,
                loading: false,
                hasPendingChanges: false,
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
                this.$http.put(`iodevices/${this.deviceId}` + (confirm ? '?confirm=1' : ''), this.device, {skipErrorHandler: true})
                    .then(response => $.extend(this.device, response.body))
                    .then(() => this.loading = this.hasPendingChanges = false)
                    .catch(response => this.changeFunctionConfirmationObject = response.body);
            }, 1000),
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

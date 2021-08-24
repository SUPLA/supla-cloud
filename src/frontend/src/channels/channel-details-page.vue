<template>
    <page-container :error="error">
        <loading-cover :loading="!channel || loading">
            <div class="container"
                v-if="channel">
                <router-link :to="{name: 'device', params: {id: channel.iodeviceId}}">
                    &laquo; {{ deviceTitle }}
                </router-link>
                <pending-changes-page :header="channelTitle"
                    @cancel="cancelChanges()"
                    @save="saveChanges()"
                    :is-pending="hasPendingChanges">
                    <h4>
                        {{ $t(channel.type.caption) + (channel.type.name == 'UNSUPPORTED' ? ':' : ',') }}
                        <span v-if="channel.type.name == 'UNSUPPORTED'">{{ channel.type.id }},</span>
                        {{ $t('Channel No') }}: {{ channel.channelNumber }},
                        {{ $t(channel.function.caption) }}
                        <span v-if="channel.function.name == 'UNSUPPORTED'">({{ channel.functionId }})</span>
                        <a class="small"
                            @click="changingFunction = true">{{ $t('Change function') }}</a>
                    </h4>

                    <div class="row hidden-xs">
                        <div class="col-xs-12">
                            <dots-route></dots-route>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-sm-4">
                            <h3>{{ $t('Configuration') }}</h3>
                            <div class="hover-editable hovered text-left">
                                <dl v-if="channel.function.id">
                                    <dd>{{ $t('Channel name') }}</dd>
                                    <dt>
                                        <input type="text"
                                            class="form-control text-center"
                                            :placeholder="$t('Default')"
                                            @keydown="updateChannel()"
                                            v-model="channel.caption">
                                    </dt>
                                    <dd>{{ $t('Show on the Client’s devices') }}</dd>
                                    <dt class="text-center">
                                        <toggler v-model="channel.hidden"
                                            invert="true"
                                            :disabled="frozenShownInClientsState !== undefined"
                                            @input="updateChannel()"></toggler>
                                    </dt>
                                </dl>
                                <channel-params-form :channel="channel"
                                    @change="updateChannel()"></channel-params-form>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h3>{{ $t('Location') }}</h3>
                            <div class="form-group">
                                <square-location-chooser v-model="channel.location"
                                    :square-link-class="channel.inheritedLocation ? 'yellow' : ''"
                                    @input="onLocationChange($event)"></square-location-chooser>
                            </div>
                            <p v-if="channel.inheritedLocation"
                                class="text-muted">
                                {{ $t('Channel is assigned to the I/O device location') }}
                            </p>
                            <a v-else
                                @click="onLocationChange(null)">
                                {{ $t('Inherit I/O Device location') }}
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <h3>{{ $t('State') }}</h3>
                            <function-icon :model="channel"
                                width="100"></function-icon>
                            <channel-alternative-icon-chooser :channel="channel"
                                v-if="channelFunctionIsChosen"
                                @change="updateChannel()"></channel-alternative-icon-chooser>
                            <channel-state-table :channel="channel"
                                v-if="channelFunctionIsChosen"></channel-state-table>
                        </div>
                    </div>
                </pending-changes-page>
            </div>


            <channel-details-tabs v-if="channel && !loading"
                :channel="channel"></channel-details-tabs>

        </loading-cover>

        <channel-function-edit-modal v-if="changingFunction"
            :channel="channel"
            :loading="loading"
            @cancel="changingFunction = false"
            @confirm="changeFunction($event)"></channel-function-edit-modal>

        <channel-function-edit-confirmation :confirmation-object="changeFunctionConfirmationObject"
            v-if="changeFunctionConfirmationObject"
            @cancel="loading = changeFunctionConfirmationObject = undefined"
            @confirm="changeFunction(changeFunctionConfirmationObject.newFunction, false)"></channel-function-edit-confirmation>

    </page-container>
</template>

<script>
    import {channelTitle, deviceTitle} from "../common/filters";
    import DotsRoute from "../common/gui/dots-route.vue";
    import FunctionIcon from "./function-icon";
    import ChannelParamsForm from "./params/channel-params-form";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import ChannelAlternativeIconChooser from "./channel-alternative-icon-chooser";
    import ChannelStateTable from "./channel-state-table";
    import ChannelDetailsTabs from "./channel-details-tabs";
    import ChannelFunctionEditConfirmation from "./channel-function-edit-confirmation";
    import throttle from "lodash/throttle";
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import $ from "jquery";
    import ChannelFunctionEditModal from "@/channels/channel-function-edit-modal";

    export default {
        props: ['id'],
        components: {
            ChannelFunctionEditModal,
            PageContainer,
            PendingChangesPage,
            ChannelFunctionEditConfirmation,
            ChannelDetailsTabs,
            ChannelStateTable,
            ChannelAlternativeIconChooser,
            SquareLocationChooser,
            ChannelParamsForm,
            DotsRoute,
            FunctionIcon,
            Toggler,
        },
        data() {
            return {
                channel: undefined,
                error: false,
                loading: false,
                hasPendingChanges: false,
                changingFunction: false,
                changeFunctionConfirmationObject: undefined
            };
        },
        mounted() {
            this.fetchChannel();
        },
        methods: {
            fetchChannel() {
                this.loading = true;
                this.error = false;
                this.$http.get(`channels/${this.id}?include=iodevice,location,supportedFunctions,iodevice.location`, {skipErrorHandler: [403, 404]})
                    .then(response => {
                        this.channel = response.body;
                        this.$set(this.channel, 'enabled', !!this.channel.function.id);
                        this.hasPendingChanges = this.loading = false;
                    })
                    .catch(response => this.error = response.status);
            },
            updateChannel() {
                if (this.frozenShownInClientsState !== undefined) {
                    this.channel.hidden = !this.frozenShownInClientsState;
                }
                this.$set(this.channel, 'hasPendingChanges', true);
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.fetchChannel();
            },
            changeFunction(newFunction, safe = true) {
                const channel = {...this.channel};
                channel.function = newFunction;
                channel.altIcon = 0;
                channel.userIconId = null;
                this.loading = true;
                this.changeFunctionConfirmationObject = undefined;
                this.$http.put(`channels/${this.id}` + (safe ? '?safe=1' : ''), channel, {skipErrorHandler: [409]})
                    .then(response => $.extend(this.channel, response.body))
                    .catch(response => {
                        if (response.status === 409) {
                            this.changeFunctionConfirmationObject = response.body;
                            this.changeFunctionConfirmationObject.newFunction = newFunction;
                        }
                    })
                    .finally(() => this.changingFunction = this.loading = false);
            },
            saveChanges: throttle(function () {
                this.loading = true;
                return this.$http.put(`channels/${this.id}?safe=1`, this.channel)
                    .then(response => $.extend(this.channel, response.body))
                    .then(() => {
                        this.hasPendingChanges = false;
                        this.$set(this.channel, 'hasPendingChanges', false);
                    })
                    .finally(() => this.loading = false);
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
            channelTitle() {
                return channelTitle(this.channel, this);
            },
            deviceTitle() {
                return deviceTitle(this.channel.iodevice, this);
            },
            frozenShownInClientsState() {
                if (this.channel.config.controllingChannelId || this.channel.config.controllingSecondaryChannelId) {
                    return true;
                } else if (this.channel.function.name === 'ACTION_TRIGGER') {
                    return false;
                }
                return undefined;
            },
            channelFunctionIsChosen() {
                return this.channel.function.id > 0 && this.channel.function.name != 'UNSUPPORTED';
            }
        }
    };
</script>

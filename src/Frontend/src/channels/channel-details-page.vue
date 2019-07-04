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
                        {{ $t(channel.type.caption) + (channel.type.name == 'UNSUPPORTED' ? ':' : ',')}}
                        <span v-if="channel.type.name == 'UNSUPPORTED'">{{channel.type.id}},</span>
                        {{ $t('Channel No') }}: {{ channel.channelNumber }}
                    </h4>
                    <div class="row hidden-xs">
                        <div class="col-xs-12">
                            <dots-route></dots-route>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-sm-4">
                            <h3>{{ $t('Function') }}</h3>
                            <div class="hover-editable text-left">
                                <div class="form-group">
                                    <div class="dropdown hovered">
                                        <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                                            type="button"
                                            data-toggle="dropdown">
                                            <h4>
                                                {{ $t(channel.function.caption) }}
                                                <span v-if="channel.function.name == 'UNSUPPORTED'">({{channel.functionId}})</span>
                                            </h4>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li v-for="fnc in supportedFunctions">
                                                <a @click="onFunctionChange(fnc)"
                                                    v-show="channel.function.id != fnc.id">{{ $t(fnc.caption) }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <dl v-if="channel.function.id">
                                    <dd>{{ $t('Caption') }}</dd>
                                    <dt>
                                        <input type="text"
                                            class="form-control text-center"
                                            @keydown="updateChannel()"
                                            v-model="channel.caption">
                                    </dt>
                                    <dd>{{ $t('Show on the Client’s devices') }}</dd>
                                    <dt class="text-center">
                                        <toggler v-model="channel.hidden"
                                            invert="true"
                                            :disabled="shownInClientsAlwaysOn"
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
                                v-if="!changedFunction && channelFunctionIsChosen"></channel-state-table>
                        </div>
                    </div>
                </pending-changes-page>
            </div>


            <channel-details-tabs v-if="channel && (!changedFunction || !loading)"
                :channel="channel"></channel-details-tabs>

        </loading-cover>
        <channel-function-edit-confirmation :confirmation-object="changeFunctionConfirmationObject"
            v-if="changeFunctionConfirmationObject"
            @cancel="loading = changeFunctionConfirmationObject = undefined"
            @confirm="saveChanges(true)"></channel-function-edit-confirmation>
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
    import ChannelActionExecutor from "./action/channel-action-executor";

    export default {
        props: ['id'],
        components: {
            ChannelActionExecutor,
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
                changedFunction: false,
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
                this.$http.get(`channels/${this.id}?include=iodevice,location,supportedFunctions,relationsCount`, {skipErrorHandler: [403, 404]})
                    .then(response => {
                        this.channel = response.body;
                        this.$set(this.channel, 'enabled', !!this.channel.function.id);
                        this.changedFunction = this.hasPendingChanges = this.loading = false;
                    })
                    .catch(response => this.error = response.status);
            },
            updateChannel() {
                if (this.shownInClientsAlwaysOn) {
                    this.channel.hidden = false;
                }
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.fetchChannel();
            },
            saveChanges: throttle(function (confirm = false) {
                this.loading = true;
                this.changeFunctionConfirmationObject = undefined;
                this.$http.put(`channels/${this.id}` + (confirm ? '?confirm=1' : ''), this.channel, {skipErrorHandler: true})
                    .then(response => $.extend(this.channel, response.body))
                    .then(() => this.loading = this.changedFunction = this.hasPendingChanges = false)
                    .catch(response => this.changeFunctionConfirmationObject = response.body);
            }, 1000),
            onLocationChange(location) {
                this.channel.inheritedLocation = !location;
                if (!location) {
                    location = this.channel.iodevice.location;
                }
                this.$set(this.channel, 'location', location);
                this.updateChannel();
            },
            onFunctionChange(fnc) {
                this.changedFunction = true;
                this.$set(this.channel, 'state', {});
                this.$set(this.channel, 'function', fnc);
                this.channel.altIcon = 0;
                this.channel.userIconId = null;
                this.channel.textParam1 = null;
                this.channel.textParam2 = null;
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
            supportedFunctions() {
                return [].concat.apply([{id: 0, caption: 'None', name: 'NONE'}], this.channel.supportedFunctions);
            },
            shownInClientsAlwaysOn() {
                if (this.channel.function.name.match(/^OPENINGSENSOR/)) {
                    return +this.channel.param1 > 0 || +this.channel.param2 > 0;
                } else {
                    return false;
                }
            },
            channelFunctionIsChosen() {
                return this.channel.function.id > 0 && this.channel.function.name != 'UNSUPPORTED';
            }
        }
    };
</script>

<style lang="scss">
    .dropdown h4 {
        margin: 0;
        display: inline-block;
    }
</style>

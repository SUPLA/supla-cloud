<template>
    <page-container :error="error">
        <loading-cover :loading="!channel || loading">
            <div class="container"
                v-if="channel">
                <div class="d-flex mt-3">
                    <div class="flex-grow-1">
                        <h1 v-title class="m-0">{{ channelTitle }}</h1>
                        <h4>
                            {{ $t(channel.type.caption) + (channel.type.name == 'UNSUPPORTED' ? ':' : ',') }}
                            <span v-if="channel.type.name == 'UNSUPPORTED'">{{ channel.type.id }},</span>
                            {{ $t('ID') }}:
                            {{ channel.id }},
                            {{ $t('Channel No') }}: {{ channel.channelNumber }}
                        </h4>
                    </div>
                    <div>
                        <ChannelDeleteButton :channel="channel"/>
                    </div>
                </div>
                <ChannelConflictDetailsWarning :channel="channel" v-if="channel.conflictDetails"/>
                <div class="alert alert-warning"
                    v-if="channelsStore.all[channel.id]?.state?.connectedCode === 'OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED'">
                    {{ $t('This device cannot be remotely awakened. We are awaiting communication from the device...') }}
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="details-page-block">
                            <h3 class="text-center">{{ $t('Configuration') }}</h3>
                            <div class="hover-editable hovered">
                                <dl>
                                    <dd>{{ $t('Function') }}</dd>
                                    <dt class="text-center"
                                        v-tooltip="hasPendingChanges && $t('Save or discard configuration changes first.')">
                                        <a class="btn btn-default btn-block btn-wrapped"
                                            :class="{disabled: hasPendingChanges}"
                                            @click="changingFunction = true">
                                            <p class="no-margin text-default">
                                                {{ $t(channel.function.caption) }}
                                                <span v-if="channel.function.name == 'UNSUPPORTED'">({{ channel.functionId }})</span>
                                            </p>
                                            <span class="small"
                                                v-if="channel.function.id">
                                                {{ $t('Change function') }}
                                            </span>
                                            <span class="small"
                                                v-else>
                                                {{ $t('Choose channel function') }}
                                            </span>
                                        </a>
                                    </dt>
                                </dl>
                                <form @submit.prevent="saveChanges()">
                                    <dl>
                                        <dd>{{ $t('Channel name') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control text-center"
                                                :placeholder="$t('Default')"
                                                @keydown="updateChannel()"
                                                v-model="channel.caption">
                                        </dt>
                                    </dl>
                                    <dl v-if="channel.function.id && frozenShownInClientsState !== false">
                                        <dd>{{ $t('Show on the Client’s devices') }}</dd>
                                        <dt class="text-center">
                                            <toggler v-model="channel.hidden"
                                                invert="true"
                                                :disabled="frozenShownInClientsState !== undefined"
                                                @input="updateChannel()"></toggler>
                                        </dt>
                                    </dl>
                                    <channel-params-form :channel="channel"
                                        @save="saveChanges()"
                                        @change="updateChannel()"></channel-params-form>
                                    <transition-expand>
                                        <ConfigConflictWarning @refresh="refreshChannelConfig()" v-if="configConflictDetected"
                                            class="mt-3 mb-0"/>
                                    </transition-expand>
                                    <transition-expand>
                                        <div class="text-center mt-3" v-if="!configConflictDetected && hasPendingChanges">
                                            <a class="btn btn-grey mx-1"
                                                @click="cancelChanges()">
                                                <i class="pe-7s-back"></i>
                                                {{ $t('Cancel changes') }}
                                            </a>
                                            <button class="btn btn-white mx-1"
                                                type="submit">
                                                <i class="pe-7s-diskette"></i>
                                                {{ $t('Save changes') }}
                                            </button>
                                        </div>
                                    </transition-expand>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="details-page-block">
                            <h3 class="text-center">{{ $t('Device') }}</h3>
                            <div class="form-group">
                                <device-tile :device="channel.iodevice"></device-tile>
                            </div>
                        </div>
                        <div class="details-page-block">
                            <h3 class="text-center">{{ $t('Location') }}</h3>
                            <div class="form-group"
                                v-tooltip.bottom="(hasPendingChanges && $t('Save or discard configuration changes first.')) || (channel.inheritedLocation && $t('Channel is assigned to the I/O device location'))">
                                <SquareLocationChooser v-model="channel.location"
                                    :disabled="hasPendingChanges"
                                    :square-link-class="channel.inheritedLocation ? 'yellow' : ''"
                                    @chosen="(location) => changeLocation(location)"/>
                            </div>
                            <div class="text-center">
                                <a v-if="!channel.inheritedLocation && !hasPendingChanges"
                                    @click="changeLocation(null)">
                                    {{ $t('Inherit I/O Device location') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="details-page-block">
                            <h3 class="text-center">{{ $t('State') }}</h3>
                            <div class="text-center">
                                <function-icon :model="channelsStore.all[channel.id] || channel" width="100"
                                    :flexible-width="true"></function-icon>
                                <div v-if="channelFunctionIsChosen">
                                    <span v-if="hasPendingChanges"
                                        v-tooltip.bottom="$t('Save or discard configuration changes first.')">
                                        {{ $t('Change icon') }}
                                    </span>
                                    <channel-alternative-icon-chooser :channel="channel"
                                        v-else
                                        @change="saveChanges()"></channel-alternative-icon-chooser>
                                </div>
                                <channel-state-table class="py-3"
                                    :channel="channel"
                                    v-if="channelFunctionIsChosen && !loading"></channel-state-table>
                                <ChannelMuteAlarmButton :channel="channelsStore.all[channel.id] || channel"/>
                            </div>
                        </div>
                        <div class="details-page-block" v-if="hasActionsToExecute">
                            <h3 class="text-center">{{ $t('Actions') }}</h3>
                            <div class="pt-3">
                                <channel-action-executor :subject="channel"></channel-action-executor>
                            </div>
                        </div>
                        <ChannelDependenciesList :channel="channelsStore.all[channel.id] || channel"/>
                    </div>
                </div>
            </div>

            <ChannelDetailsTabs v-if="channel && !loading" :channel-id="channel.id"/>

        </loading-cover>

        <channel-action-executor-modal v-if="executingAction"
            @close="executingAction = false"
            :subject="channel"></channel-action-executor-modal>

        <channel-function-edit-modal v-if="changingFunction"
            :channel="channel"
            :loading="loading"
            @cancel="changingFunction = false"
            @confirm="changeFunction($event)"></channel-function-edit-modal>

        <dependencies-warning-modal
            header-i18n="Are you sure you want to change channel’s function?"
            description-i18n="Changing channel’s function will also result in the following changes."
            deleting-header-i18n="The items below depend on this channel function, so they will be deleted."
            removing-header-i18n="Channel reference will be removed from the items below."
            :loading="loading"
            v-if="changeFunctionConfirmationObject"
            :dependencies="changeFunctionConfirmationObject"
            @cancel="loading = changeFunctionConfirmationObject = undefined"
            @confirm="changeFunction(changeFunctionConfirmationObject.newFunction, false)"></dependencies-warning-modal>

        <dependencies-warning-modal
            header-i18n="Are you sure you want to change channel’s location?"
            description-i18n="Changing the location will also imply changing the location of the following items."
            deleting-header-i18n=""
            removing-header-i18n=""
            :loading="loading"
            v-if="changeLocationConfirmationObject"
            :dependencies="changeLocationConfirmationObject"
            @cancel="loading = changeLocationConfirmationObject = undefined"
            @confirm="changeLocation(changeLocationConfirmationObject.newLocation, false)"></dependencies-warning-modal>

        <dependencies-warning-modal
            header-i18n="Are you sure you want to change channel’s visibility?"
            description-i18n="Changing channel’s visibility will also change the visibility of other items."
            removing-header-i18n=""
            :loading="loading"
            v-if="saveConfigConfirmationObject"
            :dependencies="saveConfigConfirmationObject"
            @cancel="loading = saveConfigConfirmationObject = undefined"
            @confirm="saveChanges(false)"></dependencies-warning-modal>

        <modal v-if="sleepingDeviceWarning"
            :header="$t('Device might be sleeping')"
            @confirm="sleepingDeviceWarning = false">
            {{ $t('If the device is currently in the sleep mode, the configuration changes you made will be applied after it is woken up.') }}
        </modal>
    </page-container>
</template>

<script>
    import {channelTitle} from "../common/filters";
    import FunctionIcon from "./function-icon";
    import ChannelParamsForm from "./params/channel-params-form";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import ChannelAlternativeIconChooser from "./channel-alternative-icon-chooser";
    import ChannelStateTable from "./channel-state-table";
    import ChannelDetailsTabs from "./channel-details-tabs";
    import throttle from "lodash/throttle";
    import Toggler from "../common/gui/toggler";
    import PageContainer from "../common/pages/page-container";
    import ChannelFunctionEditModal from "@/channels/channel-function-edit-modal";
    import DeviceTile from "@/devices/list/device-tile";
    import EventBus from "@/common/event-bus";
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal";
    import ChannelActionExecutor from "@/channels/action/channel-action-executor";
    import ChannelActionExecutorModal from "./action/channel-action-executor-modal";
    import TransitionExpand from "../common/gui/transition-expand";
    import {extendObject} from "@/common/utils";
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
    import ChannelConflictDetailsWarning from "@/channels/channel-conflict-details-warning.vue";
    import ChannelDeleteButton from "@/channels/channel-delete-button.vue";
    import {mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelDependenciesList from "@/channels/channel-dependencies-list.vue";
    import ChannelMuteAlarmButton from "@/channels/action/channel-mute-alarm-button.vue";

    export default {
        props: ['id'],
        components: {
            ChannelMuteAlarmButton,
            ChannelDependenciesList,
            ChannelDeleteButton,
            ChannelConflictDetailsWarning,
            ConfigConflictWarning,
            TransitionExpand,
            ChannelActionExecutorModal,
            ChannelActionExecutor,
            DependenciesWarningModal,
            DeviceTile,
            ChannelFunctionEditModal,
            PageContainer,
            ChannelDetailsTabs,
            ChannelStateTable,
            ChannelAlternativeIconChooser,
            SquareLocationChooser,
            ChannelParamsForm,
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
                executingAction: false,
                changeFunctionConfirmationObject: undefined,
                changeLocationConfirmationObject: undefined,
                saveConfigConfirmationObject: undefined,
                sleepingDeviceWarning: false,
                onChangeListener: undefined,
                configConflictDetected: false,
            };
        },
        mounted() {
            this.fetchChannel();
            this.onChangeListener = () => this.refreshChannelConfig(false);
            EventBus.$on('channel-updated', this.onChangeListener);
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.onChangeListener);
        },
        methods: {
            channelRequest() {
                return this.$http.get(`channels/${this.id}?include=iodevice,location,supportedFunctions,iodevice.location,actionTriggers`, {skipErrorHandler: [403, 404]});
            },
            fetchChannel() {
                this.channel = undefined;
                this.loading = true;
                this.error = false;
                this.channelRequest()
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
                this.$set(this.channel, 'state', {});
                const channel = {...this.channel};
                channel.function = newFunction;
                channel.altIcon = 0;
                channel.userIconId = null;
                this.loading = true;
                this.changeFunctionConfirmationObject = undefined;
                this.$http.put(`channels/${this.id}` + (safe ? '?safe=1' : ''), channel, {skipErrorHandler: [409]})
                    .then(response => extendObject(this.channel, response.body))
                    .then(() => this.afterSave())
                    .then(() => this.$router.replace({name: 'channel', params: {id: this.channel.id}}))
                    .catch(response => {
                        if (response.status === 409) {
                            if (response.body.dependencies) {
                                this.changeFunctionConfirmationObject = response.body;
                                this.changeFunctionConfirmationObject.newFunction = newFunction;
                            } else {
                                this.configConflictDetected = true;
                            }
                        }
                    })
                    .finally(() => this.changingFunction = this.loading = false);
            },
            saveChanges: throttle(function (safe = true) {
                this.loading = true;
                return this.$http.put(`channels/${this.id}${safe ? '?safe=1' : ''}`, this.channel, {skipErrorHandler: [409]})
                    .then(response => extendObject(this.channel, response.body))
                    .then(() => this.channelsStore.fetchChannel(this.channel.id))
                    .then(() => {
                        this.saveConfigConfirmationObject = undefined;
                        this.hasPendingChanges = false;
                        this.$set(this.channel, 'hasPendingChanges', false);
                        EventBus.$emit('channel-updated');
                    })
                    .then(() => this.afterSave())
                    .catch(response => {
                        if (response.status === 409) {
                            if (response.body.dependencies) {
                                this.saveConfigConfirmationObject = response.body;
                            } else {
                                this.configConflictDetected = true;
                            }
                        }
                    })
                    .finally(() => this.loading = false);
            }, 1000),
            changeLocation(location, safe = true) {
                this.loading = true;
                const request = location ? {locationId: location.id} : {inheritedLocation: true};
                return this.$http.put(`channels/${this.id}${safe ? '?safe=1' : ''}`, request, {skipErrorHandler: [409]})
                    .then(() => {
                        this.changeLocationConfirmationObject = undefined;
                        this.channel.inheritedLocation = !location;
                        if (!location) {
                            location = this.channel.iodevice.location;
                        }
                        this.$set(this.channel, 'location', location);
                    })
                    .catch(response => {
                        if (response.status === 409) {
                            this.changeLocationConfirmationObject = response.body;
                            this.changeLocationConfirmationObject.newLocation = location;
                        }
                    })
                    .finally(() => this.loading = false);
            },
            afterSave() {
                if (this.channel.iodevice.sleepModeEnabled) {
                    this.sleepingDeviceWarning = true;
                }
                return this.channelsStore.fetchAll(true);
            },
            refreshChannelConfig(showLoading = true) {
                this.loading = showLoading;
                this.channelsStore.fetchChannel(this.channel.id).then((channel) => {
                    this.channel.relationsCount = channel.relationsCount;
                    this.channel.config = channel.config;
                    this.channel.configBefore = channel.configBefore;
                    this.configConflictDetected = false;
                    this.loading = false;
                });
            }
        },
        computed: {
            channelTitle() {
                return channelTitle(this.channel);
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
            },
            hasActionsToExecute() {
                const noApiActionFunctions = ['VALVEPERCENTAGE'];
                return this.channel.possibleActions?.length && !noApiActionFunctions.includes(this.channel.function.name);
            },
            ...mapStores(useChannelsStore),
        },
        watch: {
            id(l, a) {
                if (+l !== +a) {
                    this.fetchChannel();
                }
            }
        }
    };
</script>

<template>
    <page-container :error="error">
        <loading-cover :loading="loading"
            class="channel-group-details">
            <div v-if="directLink">
                <div class="container">
                    <pending-changes-page :header="$t(directLink.id ? 'Direct link' : 'New direct link') + (directLink.id ? ' ID'+ directLink.id : '')"
                        @cancel="cancelChanges()"
                        @save="saveDirectLink()"
                        :deletable="!isNew"
                        @delete="deleteConfirm = true"
                        :is-pending="hasPendingChanges && !isNew">
                        <direct-link-preview v-if="fullUrl"
                            :url="fullUrl"
                            :possible-actions="possibleActions"
                            :allowed-actions="allowedActions"></direct-link-preview>
                        <div class="row hidden-xs"
                            v-if="!isNew">
                            <div class="col-xs-12">
                                <dots-route></dots-route>
                            </div>
                        </div>
                        <div class="form-group"
                            v-if="!isNew">
                            <div class="row text-center">
                                <div class="col-sm-4">
                                    <h3>{{ $t('Details') }}</h3>
                                    <div class="hover-editable text-left">
                                        <dl>
                                            <dd>{{ $t('Caption') }}</dd>
                                            <dt>
                                                <input type="text"
                                                    class="form-control"
                                                    @keydown="channelGroupChanged()"
                                                    v-model="directLink.caption">
                                            </dt>
                                            <dd>{{ $t('Allowed actions') }}</dd>
                                            <dt>
                                                <div class="row">
                                                    <div v-for="action in possibleActions"
                                                        class="col-sm-3 text-center">
                                                        <toggler
                                                            :label="action.caption"
                                                            @input="directLinkChanged()"
                                                            v-model="allowedActions[action.name]"></toggler>
                                                    </div>
                                                </div>
                                            </dt>
                                            <dd>{{ $t('Active between') }}</dd>
                                            <dt>
                                                <date-range-picker v-model="directLink.activeDateRange"
                                                    @input="directLinkChanged()"></date-range-picker>
                                            </dt>
                                            <dd>{{ $t('Executions limit') }}</dd>
                                            <dt>
                                                <input v-model="directLink.executionsLimit"
                                                    class="form-control"
                                                    type="number"
                                                    min="0"
                                                    @input="directLinkChanged()">
                                            </dt>
                                        </dl>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h3>{{ $t('Subject') }}</h3>
                                    <div class="text-left">
                                        <channel-tile :model="directLink.subject"></channel-tile>
                                    </div>
                                    <!--<square-location-chooser v-model="channelGroup.location"-->
                                    <!--@input="onLocationChange($event)"></square-location-chooser>-->
                                </div>
                                <div class="col-sm-4">
                                    <h3>{{ $t('Executions history') }}</h3>
                                    <direct-link-audit :direct-link="directLink"></direct-link-audit>
                                    <!--<div v-if="channelGroup.function">-->
                                    <!--<function-icon :model="channelGroup"-->
                                    <!--width="100"></function-icon>-->
                                    <!--<h4>{{ $t(channelGroup.function.caption) }}</h4>-->
                                    <!--<channel-alternative-icon-chooser :channel="channelGroup"-->
                                    <!--@change="channelGroupChanged()"></channel-alternative-icon-chooser>-->
                                    <!--</div>-->
                                    <!--<div v-else-if="isNewGroup">-->
                                    <!--<i class="pe-7s-help1"-->
                                    <!--style="font-size: 3em"></i>-->
                                    <!--</div>-->
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
                <div v-if="isNew">
                    <h3 class="text-center">{{ $t('Choose an item that should be managed by this link') }}</h3>
                    <div class="row">
                        <div class="col-lg-4 col-lg-offset-4">
                            <channels-dropdown @input="chooseSubjectForNewLink($event)"></channels-dropdown>
                        </div>
                    </div>
                </div>

            </div>
        </loading-cover>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import FunctionIcon from "../channels/function-icon.vue";
    import DotsRoute from "../common/gui/dots-route.vue";
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import ChannelsDropdown from "../devices/channels-dropdown";
    import ChannelTile from "../channels/channel-tile";
    import DirectLinkPreview from "./direct-link-preview";
    import DateRangePicker from "./date-range-picker";
    import DirectLinkAudit from "./direct-link-audit";

    export default {
        props: ['id', 'item'],
        components: {
            DirectLinkAudit,
            DateRangePicker,
            DirectLinkPreview,
            ChannelTile,
            ChannelsDropdown,
            PageContainer,
            PendingChangesPage,
            Toggler,
            DotsRoute,
            FunctionIcon,
        },
        data() {
            return {
                loading: false,
                directLink: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                allowedActions: {}
            };
        },
        mounted() {
            this.fetch();
        },
        methods: {
            fetch() {
                this.hasPendingChanges = false;
                if (this.id && this.id != 'new') {
                    this.loading = true;
                    this.error = false;
                    this.$http.get(`direct-links/${this.id}?include=subject,iodevice`, {skipErrorHandler: [403, 404]})
                        .then(response => this.directLink = response.body)
                        .then(() => this.calculateAllowedActions())
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                }
                else {
                    this.directLink = {};
                }
            },
            calculateAllowedActions() {
                this.allowedActions = {};
                this.possibleActions.forEach(possibleAction => {
                    this.$set(this.allowedActions, possibleAction.name, this.directLink.allowedActions.indexOf(possibleAction.name) >= 0);
                });
            },
            chooseSubjectForNewLink(subject) {
                const toSend = {
                    subjectType: 'channel',//subject.subjectType,
                    subjectId: subject.id,
                    caption: this.$t('Direct Link for #') + subject.id,
                    allowedActions: ['read'],
                };
                this.$http.post('direct-links', toSend).then(response => {
                    const newLink = response.body;
                    this.$emit('add', newLink);
                }).catch(() => this.$emit('delete'));
            },
            directLinkChanged() {
                this.hasPendingChanges = true;
            },
            saveDirectLink() {
                const toSend = Vue.util.extend({}, this.directLink);
                toSend.allowedActions = this.currentlyAllowedActions;
                this.loading = true;
                this.$http
                    .put('direct-links/' + this.directLink.id, toSend)
                    .then(response => this.$emit('update', response.body))
                    .then(() => this.hasPendingChanges = false)
                    .finally(() => this.loading = false);

            },
            deleteDirectLink() {
                this.loading = true;
                this.$http.delete('direct-links/' + this.directLink.id).then(() => this.$emit('delete'));
                this.directLink = undefined;
            },
            cancelChanges() {
                this.fetch();
            },
        },
        computed: {
            isNew() {
                return !this.directLink.id;
            },
            currentlyAllowedActions() {
                const actions = [];
                for (let action in this.allowedActions) {
                    if (this.allowedActions[action]) {
                        actions.push(action);
                    }
                }
                return actions;
            },
            possibleActions() {
                if (this.directLink) {
                    return [{
                        id: 1000,
                        name: 'READ',
                        caption: 'Read',
                        nameSlug: 'read'
                    }].concat(this.directLink.subject.function.possibleActions);
                }
            },
            fullUrl() {
                return this.item.url || '';
            }
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

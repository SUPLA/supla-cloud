<template>
    <page-container :error="error">
        <loading-cover :loading="loading">
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
                        <div class="row"
                            v-if="!directLink.active && directLink.inactiveReason">
                            <div class="form-group"></div>
                            <div class="col-sm-6 col-sm-offset-3">
                                <div class="alert alert-warning">
                                    {{ $t('Direct link is not working right now. Reason:') }}
                                    <strong>{{ $t(directLink.inactiveReason) }}</strong>
                                </div>
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
                                            <dd>{{ $t('Enabled') }}</dd>
                                            <dt>
                                                <toggler
                                                    @input="directLinkChanged()"
                                                    v-model="directLink.enabled"></toggler>
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
                                    <h3 class="text-center">{{ $t(directLink.subjectType == 'channel' ? 'Channel' : 'Channel group') }}</h3>
                                    <div class="text-left">
                                        <channel-tile :model="directLink.subject"
                                            v-if="directLink.subjectType == 'channel'"></channel-tile>
                                        <channel-group-tile :model="directLink.subject"
                                            v-if="directLink.subjectType == 'channelGroup'"></channel-group-tile>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <h3>{{ $t('Executions history') }}</h3>
                                    <direct-link-audit :direct-link="directLink"></direct-link-audit>
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
                <div v-if="isNew">
                    <h3 class="text-center">{{ $t('Choose an item that should be managed by this link') }}</h3>
                    <div class="row">
                        <div class="col-lg-4 col-lg-offset-4">
                            <subject-dropdown @input="chooseSubjectForNewLink($event)"></subject-dropdown>
                            <span class="help-block">
                                {{ $t('After you choose a subject, direct link will be generated. You will set all other options after creation.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </loading-cover>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteDirectLink()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this direct link?')"
            :loading="loading">
            {{ $t('You will not be able to generate a direct link with the same URL again.') }}
        </modal-confirm>
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
    import ChannelGroupTile from "../channel-groups/channel-group-tile";
    import DirectLinkPreview from "./direct-link-preview";
    import DateRangePicker from "./date-range-picker";
    import DirectLinkAudit from "./direct-link-audit";
    import SubjectDropdown from "../devices/subject-dropdown";

    export default {
        props: ['id', 'item'],
        components: {
            SubjectDropdown,
            DirectLinkAudit,
            DateRangePicker,
            DirectLinkPreview,
            ChannelTile,
            ChannelGroupTile,
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
                    this.$http.get(`direct-links/${this.id}?include=subject`, {skipErrorHandler: [403, 404]})
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
            chooseSubjectForNewLink({subject, type}) {
                const toSend = {
                    subjectType: type,
                    subjectId: subject.id,
                    caption: this.$t('Direct Link for #') + subject.id, // TODO generate it on backend when user has language in db
                    allowedActions: ['read'],
                };
                this.loading = true;
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
                    .then(response => {
                        this.$emit('update', response.body);
                        this.directLink.active = response.body.active;
                        this.directLink.inactiveReason = response.body.inactiveReason;
                    })
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
                return this.item && this.item.url || '';
            }
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

<template>
    <page-container :error="error">
        <loading-cover :loading="loading">
            <div v-if="directLink">
                <div class="container">
                    <pending-changes-page
                        :header="directLink.id ? (directLink.caption || `${$t('Direct link')} ID${directLink.id}`) : $t('New direct link')"
                        @cancel="cancelChanges()"
                        @save="saveDirectLink()"
                        :deletable="!isNew"
                        @delete="deleteConfirm = true"
                        :is-pending="hasPendingChanges && !isNew">
                        <direct-link-preview v-if="fullUrl"
                            :url="fullUrl"
                            :direct-link="directLink"
                            :possible-actions="possibleActions"
                            :allowed-actions="allowedActions"></direct-link-preview>
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
                                    <div class="details-page-block">
                                        <h3>{{ $t('Details') }}</h3>
                                        <div class="hover-editable text-left">
                                            <dl>
                                                <dd>{{ $t('Caption') }}</dd>
                                                <dt>
                                                    <input type="text"
                                                        class="form-control"
                                                        @keydown="directLinkChanged()"
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
                                                            :key="action.id"
                                                            class="col-sm-3 text-center">
                                                            <toggler
                                                                :label="actionCaption(action, directLink.subject)"
                                                                @input="directLinkChanged()"
                                                                v-model="allowedActions[action.name]"></toggler>
                                                        </div>
                                                    </div>
                                                </dt>
                                            </dl>
                                            <transition-expand>
                                                <div class="form-group"
                                                    v-if="displayOpeningSensorWarning">
                                                    <div class="alert alert-warning text-center">
                                                        {{ $t('The gate sensor must function properly in order to execute the Open and Close actions.') }}
                                                    </div>
                                                </div>
                                            </transition-expand>
                                            <dl>
                                                <dd v-tooltip="$t('Allows to perform an action only using the HTTP PATCH request.')">
                                                    {{ $t('For devices') }}
                                                    <i class="pe-7s-help1"></i>
                                                </dd>
                                                <dt>
                                                    <toggler
                                                        @input="directLinkChanged()"
                                                        v-model="directLink.disableHttpGet"></toggler>
                                                </dt>
                                            </dl>
                                            <div class="help-block small"
                                                v-if="directLink.disableHttpGet">
                                                {{ $t('When you execute the link with HTTP PATCH method, you can omit the random part of the link and send it in the request body. This is safer because such request will not be stored in any server or proxy logs, regardless of their configuration. Please find an cURL example request below.') }}
                                                <pre style="margin-top: 5px"><code>{{ examplePatchBody }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="details-page-block">
                                        <h3 class="text-center">{{ $t('actionableSubjectType_' + directLink.subjectType) }}</h3>
                                        <div class="text-left">
                                            <channel-tile :model="directLink.subject"
                                                v-if="directLink.subjectType == 'channel'"></channel-tile>
                                            <channel-group-tile :model="directLink.subject"
                                                v-if="directLink.subjectType == 'channelGroup'"></channel-group-tile>
                                            <scene-tile :model="directLink.subject"
                                                v-if="directLink.subjectType == 'scene'"></scene-tile>
                                            <ScheduleTile v-if="directLink.subjectType === 'schedule'" :model="directLink.subject"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="details-page-block">
                                        <h3>{{ $t('Execution history') }}</h3>
                                        <direct-link-audit :direct-link="directLink"></direct-link-audit>
                                    </div>
                                </div>
                            </div>
                            <h2>{{ $t('Constraints') }}</h2>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="details-page-block">
                                        <h3 class="text-center">{{ $t('Working period') }}</h3>
                                        <date-range-picker v-model="directLink.activeDateRange"
                                            @input="directLinkChanged()"></date-range-picker>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="details-page-block">
                                        <h3 class="text-center">{{ $t('Execution limit') }}</h3>
                                        <div class="executions-limit">
                                            {{ directLink.executionsLimit }}
                                        </div>
                                        <div class="text-center">
                                            <a class="btn btn-default mx-1"
                                                @click="setExecutionsLimit(undefined)">{{ $t('No limit') }}</a>
                                            <a class="btn btn-default mx-1"
                                                @click="setExecutionsLimit(1)">1</a>
                                            <a class="btn btn-default mx-1"
                                                @click="setExecutionsLimit(2)">2</a>
                                            <a class="btn btn-default mx-1"
                                                @click="setExecutionsLimit(10)">10</a>
                                            <a class="btn btn-default mx-1"
                                                @click="setExecutionsLimit(100)">100</a>
                                            <a :class="'btn btn-default mx-1 ' + (choosingCustomLimit ? 'active' : '')"
                                                @click="choosingCustomLimit = !choosingCustomLimit">{{ $t('Custom') }}</a>
                                        </div>
                                        <div v-if="choosingCustomLimit">
                                            <div class="form-group"></div>
                                            <label>{{ $t('Custom execution limit') }}</label>
                                            <input v-model="directLink.executionsLimit"
                                                class="form-control"
                                                type="number"
                                                min="0"
                                                @input="directLinkChanged()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
                <div v-if="isNew">
                    <h3 class="text-center">{{ $t('Select the item (subject) you want to control using this link') }}</h3>
                    <div class="row">
                        <div class="col-lg-4 col-lg-offset-4">
                            <subject-dropdown @input="chooseSubjectForNewLink($event)"
                                channels-dropdown-params="hasFunction=1"
                                :filter="filterOutNotDirectLinkingSubjects"></subject-dropdown>
                            <span class="help-block">
                                {{ $t('After you choose a subject, a direct link will be generated. You will be able to set all other options after its creation.') }}
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
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import ChannelTile from "../channels/channel-tile";
    import SceneTile from "../scenes/scene-tile";
    import ScheduleTile from "../schedules/schedule-list/schedule-tile";
    import ChannelGroupTile from "../channel-groups/channel-group-tile";
    import DirectLinkPreview from "./direct-link-preview";
    import DateRangePicker from "./date-range-picker";
    import DirectLinkAudit from "./direct-link-audit";
    import SubjectDropdown from "../devices/subject-dropdown";
    import AppState from "../router/app-state";
    import TransitionExpand from "../common/gui/transition-expand";
    import {actionCaption} from "@/channels/channel-helpers";

    export default {
        props: ['id', 'item'],
        components: {
            TransitionExpand,
            SubjectDropdown,
            DirectLinkAudit,
            DateRangePicker,
            DirectLinkPreview,
            ChannelTile,
            ScheduleTile,
            SceneTile,
            ChannelGroupTile,
            PageContainer,
            PendingChangesPage,
            Toggler,
        },
        data() {
            return {
                loading: false,
                directLink: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                allowedActions: {},
                choosingCustomLimit: false,
            };
        },
        mounted() {
            this.fetch();
        },
        methods: {
            actionCaption,
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
                } else {
                    this.directLink = {};
                    const subjectForNewLink = AppState.shiftTask('directLinkCreate');
                    if (subjectForNewLink) {
                        this.chooseSubjectForNewLink(subjectForNewLink);
                    }
                }
            },
            calculateAllowedActions() {
                this.allowedActions = {};
                this.possibleActions.forEach(possibleAction => {
                    this.$set(this.allowedActions, possibleAction.name, this.directLink.allowedActions.indexOf(possibleAction.name) >= 0);
                });
            },
            chooseSubjectForNewLink(subject) {
                if (subject) {
                    const toSend = {
                        subjectType: subject.ownSubjectType,
                        subjectId: subject.id,
                        allowedActions: ['read'],
                    };
                    this.loading = true;
                    this.$http.post('direct-links?include=subject', toSend).then(response => {
                        const newLink = response.body;
                        this.$emit('add', newLink);
                    }).catch(() => this.$emit('delete'));
                }
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
            setExecutionsLimit(limit) {
                this.choosingCustomLimit = false;
                this.directLink.executionsLimit = limit;
                this.directLinkChanged();
            },
            cancelChanges() {
                this.fetch();
            },
            filterOutNotDirectLinkingSubjects(subject) {
                return !['ACTION_TRIGGER'].includes(subject.function.name);
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
                if (this.directLink && this.directLink.subject) {
                    // OPEN and CLOSE actions are not supported for valves via API
                    const disableOpenClose = ['VALVEPERCENTAGE'].includes(this.directLink.subject.function.name);
                    return [{
                        id: 1000,
                        name: 'READ',
                        caption: 'Read',
                        nameSlug: 'read'
                    }].concat(this.directLink.subject.possibleActions)
                        .filter(action => !disableOpenClose || (action.name != 'OPEN' && action.name != 'CLOSE'));
                }
                return [];
            },
            displayOpeningSensorWarning() {
                const isGate = ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(this.directLink.subject.function.name) >= 0;
                return isGate && (this.currentlyAllowedActions.includes('OPEN') || this.currentlyAllowedActions.includes('CLOSE'));
            },
            fullUrl() {
                return this.item && this.item.url || '';
            },
            urlWithoutSecret() {
                return this.$user.serverUrl + '/direct/' + this.directLink.id;
            },
            linkSecret() {
                return this.fullUrl ? this.fullUrl.substr(this.fullUrl.lastIndexOf('/') + 1) : this.$t('YOUR LINK CODE');
            },
            examplePatchBody() {
                return `curl -s -H "Content-Type: application/json" -H "Accept: application/json" -X PATCH ` +
                    `-d '{"code":"${this.linkSecret}","action":"read"}' ${this.urlWithoutSecret}`;
            }
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .executions-limit {
        font-size: 3em;
        font-weight: bold;
        color: $supla-orange;
        text-align: center;
        margin-bottom: 10px;
    }
</style>

<template>
    <page-container :error="error">
        <loading-cover :loading="loading">
            <div v-if="accessId"
                class="container">
                <pending-changes-page :header="$t('Access Identifier') + ' ID' + accessId.id"
                    @cancel="cancelChanges()"
                    @save="saveAccessId()"
                    :deletable="true"
                    @delete="deleteConfirm = true"
                    :is-pending="hasPendingChanges">

                    <div class="row"
                        v-if="!accessId.activeNow">
                        <div class="col-sm-6 col-sm-offset-3">
                            <div class="alert alert-warning mt-3">
                                {{ $t('Access ID is not active right now.') }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="details-page-block">
                                <h3 class="text-center">{{ $t('Details') }}</h3>
                                <div class="hover-editable text-left">
                                    <dl>
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt class="text-center">
                                            <toggler v-model="accessId.enabled"
                                                @input="accessIdChanged()"></toggler>
                                        </dt>
                                        <dd>{{ $t('Caption') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="accessIdChanged()"
                                                v-model="accessId.caption">
                                        </dt>
                                        <dd>{{ $t('Password') }}</dd>
                                        <dt>
                                            <password-display :password="accessId.password"
                                                editable="true"
                                                @change="updatePassword($event)"></password-display>
                                        </dt>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="details-page-block">
                                <h3 class="text-center">{{ $t('Active period') }}</h3>
                                <date-range-picker v-model="activeDateRange"
                                    @input="accessIdChanged()"></date-range-picker>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="details-page-block">
                                <h3>{{ $t('Working schedule') }}</h3>
                                <div class="form-group text-center">
                                    <label>
                                        <label class="checkbox2 checkbox2-grey">
                                            <input type="checkbox"
                                                v-model="useWorkingSchedule"
                                                @change="accessIdChanged()">
                                            {{ $t('Use working schedule for this access identifier') }}
                                        </label>
                                    </label>
                                </div>
                                <transition-expand>
                                    <week-schedule-selector v-if="useWorkingSchedule"
                                        class="mode-1-green"
                                        v-model="activeHours"
                                        @input="accessIdChanged()"></week-schedule-selector>
                                </transition-expand>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="details-page-block">
                                <h3>{{ $t('Locations') }} ({{ accessId.locations.length }})</h3>
                                <table class="table table-hover"
                                    v-if="accessId.locations.length">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ $t('Password') }}</th>
                                        <th>{{ $t('Caption') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="location in accessId.locations"
                                        :key="location.id"
                                        v-go-to-link-on-row-click>
                                        <td>
                                            <router-link :to="{name: 'location', params: {id: location.id}}">{{ location.id }}</router-link>
                                        <td>
                                            <password-display :password="location.password"></password-display>
                                        </td>
                                        <td>{{ location.caption }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <a @click="assignLocations = true">
                                    <i class="pe-7s-more"></i>
                                    {{ $t('Assign Locations') }}
                                </a>
                                <location-chooser v-if="assignLocations"
                                    :selected="accessId.locations"
                                    @cancel="assignLocations = false"
                                    @confirm="updateLocations($event)"></location-chooser>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="details-page-block">
                                <h3>{{ $t('Client’s Apps') }} ({{ accessId.clientApps.length }})</h3>
                                <table class="table table-hover"
                                    v-if="accessId.clientApps.length">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ $t('Caption') }}</th>
                                        <th>{{ $t('Last access') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="app in accessId.clientApps"
                                        :key="app.id">
                                        <td>{{ app.id }}</td>
                                        <td>{{ app.caption }}</td>
                                        <td>{{ app.lastAccessDate | formatDateTime }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <a @click="assignClientApps = true">
                                    <i class="pe-7s-more"></i>
                                    {{ $t('Assign Client apps') }}
                                </a>
                                <client-app-chooser v-if="assignClientApps"
                                    :selected="accessId.clientApps"
                                    @cancel="assignClientApps = false"
                                    @confirm="updateClientApps($event)"></client-app-chooser>
                            </div>
                        </div>
                    </div>
                </pending-changes-page>
            </div>

            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteAccessId()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure?')"
                :loading="loading">
                {{ $t('Confirm if you want to remove Access Identifier') }}
            </modal-confirm>
        </loading-cover>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PasswordDisplay from "../common/gui/password-display";
    import Toggler from "../common/gui/toggler";
    import LocationChooser from "../locations/location-chooser";
    import ClientAppChooser from "../client-apps/client-app-chooser";
    import PageContainer from "../common/pages/page-container";
    import DateRangePicker from "../direct-links/date-range-picker";
    import WeekScheduleSelector from "@/activity/week-schedule-selector.vue";
    import {mapValues, pickBy} from "lodash";
    import TransitionExpand from "../common/gui/transition-expand";

    export default {
        components: {
            TransitionExpand,
            WeekScheduleSelector,
            DateRangePicker,
            PageContainer,
            ClientAppChooser,
            LocationChooser,
            Toggler,
            PasswordDisplay,
            PendingChangesPage,
        },
        props: ['id'],
        data() {
            return {
                loading: false,
                accessId: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                assignLocations: false,
                assignClientApps: false,
                useWorkingSchedule: false,
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                this.hasPendingChanges = false;
                this.loading = true;
                if (this.id && this.id != 'new') {
                    this.error = false;
                    this.$http.get(`accessids/${this.id}?include=locations,clientApps,password,activeNow`, {skipErrorHandler: [403, 404]})
                        .then(response => this.accessId = response.body)
                        .then(() => this.useWorkingSchedule = !!this.accessId.activeHours)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                } else {
                    this.$http.post('accessids', {}).then(response => this.$emit('add', response.body)).catch(() => this.$emit('delete'));
                }
            },
            saveAccessId() {
                if (!this.useWorkingSchedule) {
                    this.accessId.activeHours = {};
                }
                const toSend = Vue.util.extend({}, this.accessId);
                this.loading = true;
                this.$http.put('accessids/' + this.accessId.id, toSend)
                    .then((response) => {
                        this.$emit('update', response.body);
                        this.initForModel();
                    })
                    .finally(() => this.loading = this.hasPendingChanges = false);
            },
            deleteAccessId() {
                this.loading = true;
                this.$http.delete('accessids/' + this.accessId.id)
                    .then(() => this.$emit('delete'))
                    .then(() => this.accessId = undefined)
                    .catch(() => this.loading = false);
            },
            accessIdChanged() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.initForModel();
            },
            updatePassword(password) {
                this.accessId.password = password;
                this.accessIdChanged();
            },
            updateLocations(locations) {
                this.accessId.locations = locations;
                this.accessIdChanged();
                this.assignLocations = false;
            },
            updateClientApps(clientApps) {
                this.accessId.clientApps = clientApps;
                this.accessIdChanged();
                this.assignClientApps = false;
            },
        },
        computed: {
            activeDateRange: {
                get() {
                    return {dateStart: this.accessId.activeFrom, dateEnd: this.accessId.activeTo};
                },
                set(dates) {
                    this.$set(this.accessId, 'activeFrom', dates.dateStart || null);
                    this.$set(this.accessId, 'activeTo', dates.dateEnd || null);
                }
            },
            activeHours: {
                get() {
                    if (this.accessId.activeHours) {
                        return mapValues(this.accessId.activeHours, (hours) => {
                            const hoursDef = {};
                            [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                            return hoursDef;
                        });
                    } else {
                        return {};
                    }
                },
                set(weekSchedule) {
                    this.accessId.activeHours = mapValues(weekSchedule, (hours) => {
                        return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
                    });
                }
            }
        },
        watch: {
            id() {
                this.initForModel();
            }
        }
    };
</script>

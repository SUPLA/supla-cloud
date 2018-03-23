<template>
    <loading-cover :loading="loading">
        <div v-if="accessId"
            class="container">
            <pending-changes-page :header="$t('Access Identifier') + ' ID' + accessId.id"
                @cancel="cancelChanges()"
                @save="saveAccessId()"
                deletable="true"
                @delete="deleteConfirm = true"
                :is-pending="hasPendingChanges">

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-8">
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
                                        @change="accessIdChanged()"
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

                <div class="row">
                    <div class="col-sm-6">
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
                                v-go-to-link-on-row-click>
                                <td><a :href="'/locations/' + location.id | withBaseUrl">{{ location.id }}</a></td>
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
                    <div class="col-sm-6">
                        <h3>{{ $t('Client\'s Apps') }} ({{ accessId.clientApps.length }})</h3>
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
                            <tr v-for="app in accessId.clientApps">
                                <td>{{ app.id }}</td>
                                <td>{{ app.caption }}</td>
                                <td>{{ app.lastAccessDate | moment("LT L") }}</td>
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
</template>

<script>
    import Vue from "vue";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PasswordDisplay from "../common/gui/password-display";
    import Toggler from "../common/gui/toggler";
    import LocationChooser from "../locations/location-chooser";
    import ClientAppChooser from "../client-apps/client-app-chooser";
    import EmptyListPlaceholder from "src/common/gui/empty-list-placeholder";

    export default {
        components: {
            ClientAppChooser,
            LocationChooser,
            Toggler,
            PasswordDisplay,
            PendingChangesPage,
            EmptyListPlaceholder
        },
        props: ['id'],
        data() {
            return {
                loading: false,
                accessId: undefined,
                deleteConfirm: false,
                hasPendingChanges: false,
                assignLocations: false,
                assignClientApps: false,
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
                    this.$http.get(`accessids/${this.id}?include=locations,clientApps,password`)
                        .then(response => this.accessId = response.body)
                        .finally(() => this.loading = false);
                } else {
                    this.$http.post('accessids', {}).then(response => this.$emit('add', response.body)).catch(() => this.$emit('delete'));
                }
            },
            saveAccessId() {
                const toSend = Vue.util.extend({}, this.accessId);
                this.loading = true;
                this.$http.put('accessids/' + this.accessId.id, toSend)
                    .then(response => this.$emit('update', response.body))
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
        watch: {
            id() {
                this.initForModel();
            }
        }
    };
</script>

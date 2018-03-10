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

    export default {
        components: {
            Toggler,
            PasswordDisplay,
            PendingChangesPage
        },
        props: ['model'],
        data() {
            return {
                loading: false,
                accessId: undefined,
                deleteConfirm: false,
                hasPendingChanges: false
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                this.hasPendingChanges = false;
                this.loading = true;
                if (this.model.id) {
                    this.$http.get(`accessids/${this.model.id}?include=locations,clientApps,password`)
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
        },
        watch: {
            model() {
                this.initForModel();
            }
        }
    };
</script>

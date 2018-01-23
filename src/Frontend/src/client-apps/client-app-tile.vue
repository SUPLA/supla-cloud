<template>
    <div>
        <flipper :flipped="!!editingModel">
            <square-link :class="'clearfix pointer ' + (app.enabled ? 'green' : 'grey')"
                slot="front"
                @click="edit()">
                <h3>{{app.caption}}</h3>
                <dl>
                    <dd v-if="app.caption != app.name">{{ app.name }} / </dd>
                    <dd>{{ app.softwareVersion }} / {{ app.protocolVersion }}</dd>
                    <dt></dt>
                </dl>
                <div class="separator invisible"></div>
                <dl>
                    <dd>{{ $t('Last access') }}</dd>
                    <dt>{{ app.lastAccessDate | moment("LT L") }}</dt>
                    <dd>{{ $t('from the IP') }}</dd>
                    <dt>{{ app.lastAccessIpv4 | intToIp }}</dt>
                </dl>
                <div class="separator invisible"></div>
                <dl>
                    <dd>{{ $t('Access ID') }}</dd>
                    <dt>{{ app.accessId ? app.accessId.caption : $t('None') }}</dt>
                </dl>
                <div class="square-link-label">
                    <client-app-connection-status-label :app="app"></client-app-connection-status-label>
                </div>
            </square-link>
            <square-link class="yellow"
                slot="back">
                <form @submit.prevent="save()"
                    v-if="editingModel">
                    <div class="form-group">
                        <label>{{ $t('Name') }}</label>
                        <input type="text"
                            class="form-control"
                            v-model="editingModel.caption">
                    </div>

                    <div class="form-group">
                        <label>{{ $t('Access Identifier') }}</label>
                        <select class="form-control"
                            v-model="editingModel.accessIdId">
                            <option v-for="accessId in accessIds"
                                :value="accessId.id">ID{{accessId.id}} {{ accessId.caption }}
                            </option>
                        </select>
                    </div>
                    <switches v-model="editingModel.enabled"
                        type-bold="true"
                        color="green"
                        :emit-on-mount="false"
                        :text-enabled="$t('Enabled')"
                        :text-disabled="$t('Disabled')"></switches>
                    <div class="form-group text-right">
                        <button type="button"
                            :disabled="saving"
                            @click="deleteConfirm = true"
                            class="btn btn-danger btn-sm pull-left">
                            {{ $t('Delete') }}
                        </button>
                        <button type="button"
                            :disabled="saving"
                            class="btn btn-default btn-sm"
                            @click="cancelEdit()">
                            {{ $t('Cancel') }}
                        </button>
                        <button class="btn btn-green btn-sm"
                            :disabled="saving">
                            <button-loading-dots v-if="saving"></button-loading-dots>
                            <span v-else>OK</span>
                        </button>
                    </div>
                </form>
            </square-link>
        </flipper>
        <modal-confirm v-if="deleteConfirm"
            @confirm="deleteClient()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this client?')"
            :loading="saving">
            <p>{{ $t('The client will be automatically logged out when deleted.') }}</p>
        </modal-confirm>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import Switches from "vue-switches";
    import Vue from "vue";
    import ClientAppConnectionStatusLabel from "./client-app-connection-status-label.vue";
    import {successNotification, warningNotification} from "../common/notifier";

    export default {
        props: ['app', 'accessIds'],
        components: {Switches, ButtonLoadingDots, ClientAppConnectionStatusLabel},
        data() {
            return {
                saving: false,
                editingModel: null,
                deleteConfirm: false
            };
        },
        methods: {
            edit() {
                this.editingModel = Vue.util.extend({}, this.app);
                this.editingModel.accessIdId = this.app.accessId ? this.app.accessId.id : null;
            },
            cancelEdit() {
                this.editingModel = null;
            },
            save() {
                this.saving = true;
                this.$http.put(`client-apps/${this.app.id}`, this.editingModel)
                    .then(({body}) => Vue.util.extend(this.app, body))
                    .then(() => this.editingModel = null)
                    .then(() => successNotification(this.$t('Success'), this.$t('Data saved')))
                    .then(() => this.$emit('change'))
                    .finally(() => this.saving = false);
            },
            deleteClient() {
                this.saving = true;
                this.$http.delete(`client-apps/${this.app.id}`)
                    .then(() => this.editingModel = null)
                    .then(() => this.deleteConfirm = false)
                    .then(() => warningNotification(this.$t('Information'), this.$t('Client\'s app has been deleted')))
                    .then(() => this.$emit('delete'))
                    .finally(() => this.saving = false);
            }
        }
    };
</script>

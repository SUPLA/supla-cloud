<template>
    <div>
        <flipper :flipped="!!editingModel">
            <square-link :class="'clearfix pointer ' + (app.enabled ? 'green' : 'grey')"
                slot="front"
                @click="edit()">
                <h3>{{app.name}}</h3>
                <dl>
                    <dd>{{ app.softwareVersion }} / {{ app.protocolVersion }}</dd>
                    <dt></dt>
                </dl>
                <div class="separator invisible"></div>
                <dl>
                    <dd>Ostatnie połączenie</dd>
                    <dt>{{ app.lastAccessDate | moment("LLL") }}</dt>
                    <dd>z adresu</dd>
                    <dt>{{ app.lastAccessIpv4 | intToIp }}</dt>
                </dl>
                <div class="separator invisible"></div>
                <dl>
                    <dd>ID dostępu</dd>
                    <dt>{{ app.accessId ? app.accessId.caption : 'brak' }}</dt>
                </dl>
                <div class="square-link-label">
                    <span class="label"
                        v-if="app.connected != undefined"
                        :class="app.connected ? 'label-success' : 'label-grey'">{{ app.connected ? $t('Active') : $t('Idle') }}</span>
                </div>
            </square-link>
            <square-link class="yellow"
                slot="back">
                <form @submit.prevent="save()"
                    v-if="editingModel">
                    <div class="form-group">
                        <label>Nazwa</label>
                        <input type="text"
                            class="form-control"
                            v-model="editingModel.name">
                    </div>

                    <div class="form-group">
                        <label>Identyfikator dostępu</label>
                        <select class="form-control"
                            v-model="editingModel.accessIdId">
                            <option v-for="accessId in accessIds"
                                :value="accessId.id">{{ accessId.caption }}
                            </option>
                        </select>
                    </div>
                    <switches v-model="editingModel.enabled"
                        type-bold="true"
                        color="green"
                        emit-on-mount="false"
                        :text-enabled="$t('Enabled')"
                        :text-disabled="$t('Disabled')"></switches>
                    <div class="form-group text-right">
                        <button type="button"
                            :disabled="saving"
                            @click="deleteConfirm = true"
                            class="btn btn-danger btn-sm pull-left">Usuń
                        </button>
                        <button type="button"
                            :disabled="saving"
                            class="btn btn-default btn-sm"
                            @click="cancelEdit()">Anuluj
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
            <p>{{ $t('Deleting the client will automatically log it out.') }}</p>
            <p>{{ $t('Further registration will be required to use this device again.') }}</p>
        </modal-confirm>
    </div>
</template>

<script>
    import SquareLink from "../common/square-link.vue";
    import Flipper from "../common/flipper.vue";
    import ButtonLoadingDots from "../common/button-loading-dots.vue";
    import Switches from "vue-switches";
    import Vue from "vue";
    import ClientAppDeleteConfirmModal from "./client-app-delete-confirm-modal.vue";
    import {successNotification, warningNotification} from "../common/notifier";

    export default {
        props: ['app', 'accessIds'],
        components: {SquareLink, Flipper, Switches, ButtonLoadingDots, ClientAppDeleteConfirmModal},
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
                    .finally(() => this.saving = false);
            },
            deleteClient() {
                this.saving = true;
                this.$http.delete(`client-apps/${this.app.id}`)
                    .then(() => this.editingModel = null)
                    .then(() => this.deleteConfirm = false)
                    .then(() => warningNotification(this.$t('Information'), this.$t('Client app has been deleted')))
                    .then(() => this.$emit('delete'))
                    .finally(() => this.saving = false);
            }
        }
    };
</script>

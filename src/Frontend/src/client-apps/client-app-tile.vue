<template>
    <div>
        <flipper :flipped="!!editingModel">
            <square-link :class="'clearfix pointer ' + (app.enabled ? 'green' : 'grey')"
                slot="front"
                @click="edit()">
                <h3>{{app.name}}</h3>
                <dl>
                    <dd>Zarejestrowano</dd>
                    <dt>{{ app.regDate | moment("LLL") }}</dt>
                    <dd style="padding-left: 48px">z adresu</dd>
                    <dt>{{ app.regIpv4 | intToIp }}</dt>
                    <dd>Ostatnie połączenie</dd>
                    <dt>{{ app.lastAccessDate | moment("LLL") }}</dt>
                    <dd style="padding-left: 73px">z adresu</dd>
                    <dt>{{ app.lastAccessIpv4 | intToIp}}</dt>
                    <dd>Wersja oprogramowania / protokołu</dd>
                    <dt>{{ app.softwareVersion }} / {{ app.protocolVersion }} </dt>
                </dl>
                <div class="separator"></div>
                <dl>
                    <dd>Identyfikator dostępu</dd>
                    <dt>{{ app.accessId.caption }}</dt>
                </dl>
                <span class="label square-link-label"
                    :class="app.enabled ? 'label-success' : 'label-grey'">{{ app.enabled ? $t('Enabled') : $t('Disabled') }}</span>
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
                            v-model="editingModel.accessId.id">
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
            },
            cancelEdit() {
                this.editingModel = null;
            },
            save() {
                this.saving = true;
                this.$http.put(`client-apps/${this.app.id}`, this.editingModel)
                    .then(({body}) => Vue.util.extend(this.app, body))
                    .then(() => this.editingModel = null)
                    .finally(() => this.saving = false);
            },
            deleteClient() {
                this.saving = true;
                this.$http.delete(`client-apps/${this.app.id}`)
                    .then(() => this.editingModel = null)
                    .then(() => this.deleteConfirm = false)
                    .then(() => this.$emit('delete'))
                    .finally(() => this.saving = false);
            }
        }
    };
</script>

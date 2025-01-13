<template>
    <div>
        <flipper :flipped="!!editingModel">
            <template #front>
                <client-app-tile :model="app" @click="edit()"/>
            </template>
            <template #back>
                <square-link class="yellow not-transform">
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
                            <div>
                                <a @click="assignAccessIds = true">
                                    <span v-if="editingModel.accessId">
                                        ID{{ editingModel.accessId.id }} {{ editingModel.accessId.caption }}
                                    </span>
                                    <span v-else>{{ $t('None') }}</span>
                                </a>
                            </div>
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
                                type="submit"
                                :disabled="saving">
                                <button-loading-dots v-if="saving"></button-loading-dots>
                                <span v-else>OK</span>
                            </button>
                        </div>
                    </form>
                </square-link>
            </template>
        </flipper>
        <modal-confirm v-if="deleteConfirm"
            @confirm="deleteClient()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this client?')"
            :loading="saving">
            <p>{{ $t('The client will be automatically logged out when deleted.') }}</p>
        </modal-confirm>
        <access-id-chooser v-if="assignAccessIds"
            title-i18n="Choose Access Identifier"
            :selected="editingModel.accessId"
            @cancel="assignAccessIds = false"
            @confirm="editingModel.accessId = $event; assignAccessIds = false"></access-id-chooser>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import Switches from "vue-switches";
    import Vue from "vue";
    import {successNotification, warningNotification} from "../common/notifier";
    import ClientAppTile from "./client-app-tile";
    import AccessIdChooser from "../access-ids/access-id-chooser";

    export default {
        props: ['app'],
        components: {
            AccessIdChooser,
            ClientAppTile, Switches, ButtonLoadingDots
        },
        data() {
            return {
                saving: false,
                editingModel: null,
                deleteConfirm: false,
                assignAccessIds: false
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
                    .then(() => successNotification(this.$t('Success'), this.$t('Data saved')))
                    .then(() => this.$emit('change'))
                    .finally(() => this.saving = false);
            },
            deleteClient() {
                this.saving = true;
                this.$http.delete(`client-apps/${this.app.id}`)
                    .then(() => this.editingModel = null)
                    .then(() => this.deleteConfirm = false)
                    .then(() => warningNotification(this.$t('Information'), this.$t('Client’s app has been deleted')))
                    .then(() => this.$emit('delete'))
                    .finally(() => this.saving = false);
            }
        }
    };
</script>

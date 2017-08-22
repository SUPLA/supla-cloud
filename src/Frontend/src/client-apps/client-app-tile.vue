<template>
    <flipper :flipped="!!editingModel">
        <square-link class="clearfix pointer"
            slot="front"
            @click="edit()">
            <h3>{{app.name}}</h3>
            <dl>
                <dd>Zarejestrowano</dd>
                <dt>{{ app.regDate | moment("LLL") }}</dt>
                <dd style="padding-left: 48px">z adresu</dd>
                <dt>{{ app.regIpv4 | intToIp }}</dt>
                <dd>Ostatnia aktywność</dd>
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
                :class="app.enabled ? 'label-success' : 'label-danger'">{{ app.enabled ? 'Aktywne' : 'Nieaktywne' }}</span>
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
</template>

<script>
    import SquareLink from "../common/square-link.vue";
    import Flipper from "../common/flipper.vue";
    import ButtonLoadingDots from "../common/button-loading-dots.vue";
    import Switches from "vue-switches";
    import Vue from "vue";

    export default {
        props: ['app', 'accessIds'],
        components: {SquareLink, Flipper, Switches, ButtonLoadingDots},
        data() {
            return {
                saving: false,
                editingModel: null
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
            }
        }
    };
</script>

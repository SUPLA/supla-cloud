<template>
    <page-container :error="error">
        <loading-cover :loading="loading"
            class="channel-group-details">
            <div v-if="app">
                <div class="container">
                    <pending-changes-page :header="$t(app.id ? 'OAuth application' : 'New OAuth application') + (app.id ? ' ID'+ app.id : '')"
                        @cancel="cancelChanges()"
                        @save="saveOauthApp()"
                        :deletable="app.id"
                        @delete="deleteConfirm = true"
                        :is-pending="hasPendingChanges && !isNew">
                        <div class="row">
                            <div class="col-sm-6"
                                :class="isNew ? 'col-sm-offset-3' : ''">
                                <h3 class="text-center">{{ $t('Details') }}</h3>
                                <div :class="'hover-editable text-left form-group ' + (isNew ? 'hovered' : '')">
                                    <dl>
                                        <dd>{{ $t('Name') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="appChanged()"
                                                v-model="app.name">
                                        </dt>
                                        <dd>{{ $t('Description') }}</dd>
                                        <dt>
                                                <textarea
                                                    class="form-control"
                                                    @keydown="appChanged()"
                                                    v-model="app.description"></textarea>
                                        </dt>
                                        <dd>{{ $t('Authorization callback URL') }}</dd>
                                        <dt>
                                            <input type="text"
                                                class="form-control"
                                                @keydown="appChanged()"
                                                v-model="app.redirectUris[0]">
                                        </dt>
                                    </dl>
                                </div>
                                <button v-if="isNew"
                                    type="submit"
                                    class="btn btn-green"
                                    :disabled="loading">
                                    <i class="pe-7s-plus"></i>
                                    {{ $t('Register a new OAuth application') }}
                                </button>
                            </div>
                            <div class="col-sm-6"
                                v-if="!isNew">
                                <h3 class="text-center">{{ $t('Configuration') }}</h3>
                                <h4>{{ $t('Public ID') }}</h4>
                                <pre><code>{{ app.publicId }}</code></pre>
                                <copy-button :text="app.publicId"></copy-button>
                                <h4>{{ $t('Secret') }}</h4>
                                <pre><code>{{ app.secret }}</code></pre>
                                <copy-button :text="app.secret"></copy-button>
                            </div>
                        </div>
                    </pending-changes-page>
                    <modal-confirm v-if="deleteConfirm"
                        class="modal-warning"
                        @confirm="deleteApp()"
                        @cancel="deleteConfirm = false"
                        :header="$t('Are you sure you want to delete this OAuth application?')"
                        :loading="loading">
                    </modal-confirm>
                </div>
            </div>
        </loading-cover>
    </page-container>
</template>

<script>
    import PageContainer from "src/common/pages/page-container";
    import PendingChangesPage from "src/common/pages/pending-changes-page";
    import Vue from "vue";
    import CopyButton from "../../common/copy-button";

    export default {
        props: ['id'],
        components: {CopyButton, PageContainer, PendingChangesPage},
        data() {
            return {
                loading: false,
                app: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false
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
                    this.$http.get(`oauth-clients/${this.id}?include=secret`, {skipErrorHandler: [403, 404]})
                        .then(response => this.app = response.body)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                }
                else {
                    this.app = {redirectUris: []};
                }
            },
            saveOauthApp() {
                const toSend = Vue.util.extend({}, this.app);
                this.loading = true;
                if (this.isNew) {
                    this.$http.post('oauth-clients', toSend).then(response => {
                        this.$emit('add', response.body);
                    }).catch(() => this.loading = false);
                } else {
                    this.$http
                        .put('oauth-clients/' + this.app.id, toSend)
                        .then(response => this.$emit('update', response.body))
                        .then(() => this.hasPendingChanges = false)
                        .finally(() => this.loading = false);
                }
            },
            appChanged() {
                this.hasPendingChanges = true;
            },
            deleteApp() {
                this.loading = true;
                this.$http.delete('oauth-clients/' + this.app.id).then(() => this.$emit('delete'));
                this.app = undefined;
            },
            cancelChanges() {
                this.fetch();
            },
        },
        computed: {
            isNew() {
                return !this.app.id;
            }
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

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
                        :is-pending="hasPendingChanges || !app.id">
                        <form @submit.prevent="addOauthApp()">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h3 class="text-center">{{ $t('Details') }}</h3>
                                    <div class="hover-editable text-left">
                                        <dl>
                                            <dd>{{ $t('Name') }}</dd>
                                            <dt>
                                                <input type="text"
                                                    class="form-control"
                                                    @keydown="channelGroupChanged()"
                                                    v-model="app.name">
                                            </dt>
                                            <dd>{{ $t('Description') }}</dd>
                                            <dt>
                                                <textarea
                                                    class="form-control"
                                                    @keydown="channelGroupChanged()"
                                                    v-model="app.description"></textarea>
                                            </dt>
                                            <dd>{{ $t('Authorization callback URL') }}</dd>
                                            <dt>
                                                <input type="text"
                                                    class="form-control"
                                                    @keydown="channelGroupChanged()"
                                                    v-model="app.name">
                                            </dt>
                                            <dd>{{ $t('OfflineAccess') }}</dd>
                                            <dt class="text-center">
                                                <toggler v-model="app.issueRefreshToken"
                                                    @input="channelGroupChanged()"></toggler>
                                            </dt>
                                        </dl>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <h3 class="text-center">{{ $t('Configuration') }}</h3>
                                    <h4>{{ $t('Public ID') }}</h4>
                                    <pre><code>asldfkasdjflads</code></pre>
                                    <h4>{{ $t('Secret') }}</h4>
                                    <pre><code>asldfkasfdasdfasdfasdfasfasdofjasldfjaalksflkasjdflkajsdflkadsjfwwwwwwwwwasdjflads</code></pre>
                                </div>
                            </div>
                        </form>
                    </pending-changes-page>
                </div>
            </div>
        </loading-cover>
    </page-container>
</template>

<script>
    import PageContainer from "src/common/pages/page-container";
    import PendingChangesPage from "src/common/pages/pending-changes-page";

    export default {
        props: ['id'],
        components: {PageContainer, PendingChangesPage},
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
                    this.$http.get(`applications/${this.id}?include=secret`, {skipErrorHandler: [403, 404]})
                        .then(response => this.app = response.body)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                }
                else {
                    this.app = {};
                }
            },
            saveOauthApp() {
                // const toSend = Vue.util.extend({}, this.channelGroup);
                // this.loading = true;
                // if (this.isNewGroup) {
                //     this.$http.post('channel-groups', toSend).then(response => {
                //         const newGroup = response.body;
                //         newGroup.channels = this.channelGroup.channels;
                //         this.$emit('add', newGroup);
                //     }).catch(() => this.$emit('delete'));
                // } else {
                //     this.$http
                //         .put('channel-groups/' + this.channelGroup.id, toSend)
                //         .then(response => this.$emit('update', response.body))
                //         .then(() => this.hasPendingChanges = false)
                //         .finally(() => this.loading = false);
                // }
            },
        },
    };
</script>

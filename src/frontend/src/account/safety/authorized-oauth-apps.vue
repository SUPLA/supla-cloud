<template>
    <div class="container">
        <h5>{{ $t('This is the list of applications you have granted access to your account.') }}</h5>
        <loading-cover :loading="!authorizedApps">
            <table class="table table-striped"
                v-if="authorizedApps && authorizedApps.length">
                <thead>
                <tr>
                    <th>{{ $t('Name') }}</th>
                    <th>{{ $t('Scope') }}</th>
                    <th>{{ $t('Authorized') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="authorizedApp in authorizedApps"
                    :key="authorizedApp.id">
                    <td>{{ authorizedApp.apiClient.name }}</td>
                    <td>
                        <oauth-scope-label :scope="authorizedApp.scope"></oauth-scope-label>
                    </td>
                    <td>{{ authorizedApp.authorizationDate | formatDateTime }}</td>
                    <td class="text-right">
                        <button type="button"
                            class="btn btn-red btn-xs"
                            @click="appToRevoke = authorizedApp">
                            <fa icon="times-circle"/>
                            {{ $t('Revoke') }}
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            <empty-list-placeholder v-else-if="authorizedApps"/>
        </loading-cover>
        <modal-confirm v-if="appToRevoke"
            class="modal-warning"
            @confirm="revokeApp(appToRevoke)"
            @cancel="appToRevoke = undefined"
            :header="$t('Are you sure you want to revoke permissions granted to this OAuth application?')"
            :loading="revoking">
            {{ $t('{applicationName} will not be able to interact with your account until you grant the access again.', {applicationName: appToRevoke.apiClient.name}) }}
        </modal-confirm>
    </div>
</template>

<script>
    import OauthScopeLabel from "../integrations/oauth-scope-label.vue";

    export default {
        components: {OauthScopeLabel},
        data() {
            return {
                authorizedApps: undefined,
                appToRevoke: undefined,
                revoking: false
            };
        },
        mounted() {
            this.$http.get('oauth-authorized-clients?include=client').then(response => {
                this.authorizedApps = response.body;
            });
        },
        methods: {
            revokeApp(app) {
                this.revoking = true;
                this.$http.delete('oauth-authorized-clients/' + app.id)
                    .then(() => this.authorizedApps.splice(this.authorizedApps.indexOf(app), 1))
                    .then(() => this.appToRevoke = undefined)
                    .finally(() => this.revoking = false);
            }
        }
    };
</script>

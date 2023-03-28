<template>
    <div class="container">
        <div class="clearfix left-right-header">
            <div>
                <h5>{{ $t('Personal access tokens function like ordinary OAuth access tokens but they have no expiration time.') }}</h5>
                <div class="form-group">
                    <div class="btn-group">
                        <a href="https://github.com/SUPLA/supla-cloud/wiki/Integrations#personal-access-tokens"
                            class="btn btn-white">
                            {{ $t('Full documentation') }} @ GitHub
                        </a>
                        <a :href="'/api-docs/docs.html' | withBaseUrl"
                            target="_blank"
                            class="btn btn-white">
                            {{ $t('API documentation') }}
                            <i class="pe-7s-exapnd2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div>
                <a class="btn btn-white"
                    v-if="!generatingNewToken"
                    @click="generatingNewToken = true">
                    <i class="pe-7s-plus"></i>
                    {{ $t('Generate new token') }}
                </a>
            </div>
        </div>
        <div class="well"
            v-if="generatingNewToken">
            <h3 class="no-margin-top">{{ $t('Generate new token') }}</h3>
            <personal-access-token-generate-form
                @generated="onNewToken($event)"
                @cancel="generatingNewToken = false"></personal-access-token-generate-form>
        </div>
        <div class="alert alert-info"
            v-if="latestToken">
            <div class="form-group">
                {{ $t('A new personal access token has been generated.') }}
                <strong>{{ $t('Make sure to copy it now because you won’t be able to see it again!') }}</strong>
            </div>
            <pre><code>{{ latestToken.token }}</code></pre>
            <copy-button :text="latestToken.token"></copy-button>
        </div>
        <loading-cover :loading="!tokens">
            <table class="table table-striped"
                v-if="tokens && tokens.length">
                <thead>
                <tr>
                    <th>{{ $t('Name') }}</th>
                    <th>{{ $t('Scope') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="token in tokens"
                    :key="token.id">
                    <td>{{ token.name }}</td>
                    <td>
                        <oauth-scope-label :scope="token.scope"></oauth-scope-label>
                    </td>
                    <td class="text-right">
                        <button type="button"
                            class="btn btn-red"
                            @click="tokenToRevoke = token">
                            <i class="pe-7s-close-circle"></i>
                            {{ $t('Revoke') }}
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            <empty-list-placeholder v-else-if="tokens"></empty-list-placeholder>
        </loading-cover>
        <modal-confirm v-if="tokenToRevoke"
            class="modal-warning"
            @confirm="revokeToken(tokenToRevoke)"
            @cancel="tokenToRevoke = undefined"
            :header="$t('Are you sure you want to revoke this token?')"
            :loading="revoking">
            {{ $t('Any application or device that uses the {tokenName} token will not work anymore.', {tokenName: tokenToRevoke.name}) }}
        </modal-confirm>
    </div>
</template>

<script>
    import PersonalAccessTokenGenerateForm from "./personal-access-token-generate-form.vue";
    import OauthScopeLabel from "../../integrations/oauth-scope-label.vue";
    import CopyButton from "../../../common/copy-button.vue";

    export default {
        components: {CopyButton, OauthScopeLabel, PersonalAccessTokenGenerateForm},
        data() {
            return {
                tokens: undefined,
                generatingNewToken: false,
                latestToken: undefined,
                revoking: false,
                tokenToRevoke: undefined,
            };
        },
        mounted() {
            this.$http.get('oauth-personal-tokens').then(response => {
                this.tokens = response.body;
            });
        },
        methods: {
            onNewToken(token) {
                this.tokens.unshift(token);
                this.latestToken = token;
                this.generatingNewToken = false;
            },
            revokeToken(token) {
                this.revoking = true;
                this.$http.delete('oauth-personal-tokens/' + token.id)
                    .then(() => this.tokens.splice(this.tokens.indexOf(token), 1))
                    .then(() => this.tokenToRevoke = undefined)
                    .finally(() => this.revoking = false);
            }
        }
    };
</script>

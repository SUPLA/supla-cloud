<template>
    <div>
        <div class="clearfix left-right-header">
            <div>
                <h5>{{ $t('Personal access tokens function like ordinary OAuth access tokens but they have no expiration time.') }}</h5>
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
            <div class="form-group">A new personal access token has been generated.
                <strong>Make sure to copy it now because you won’t be able to see it again!</strong></div>
            <api-setting-copy-input :label="latestToken.name"
                :value="latestToken.token"></api-setting-copy-input>
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
                <tr v-for="token in tokens">
                    <td>{{ token.name }}</td>
                    <td>
                        <oauth-scope-label :scope="token.scope"></oauth-scope-label>
                    </td>
                    <td class="text-right">
                        <button type="button"
                            class="btn btn-red">
                            <i class="pe-7s-close-circle"></i>
                            {{ $t('Revoke') }}
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            <empty-list-placeholder v-else-if="tokens"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import PersonalAccessTokenGenerateForm from "./personal-access-token-generate-form";
    import ApiSettingCopyInput from "../account-details/api-setting-copy-input";
    import OauthScopeLabel from "./oauth-scope-label";

    export default {
        components: {OauthScopeLabel, ApiSettingCopyInput, PersonalAccessTokenGenerateForm},
        data() {
            return {
                tokens: undefined,
                generatingNewToken: false,
                latestToken: undefined,
            };
        },
        mounted() {
            this.$http.get('integrations/personal-tokens').then(response => {
                this.tokens = response.body;
            });
        },
        methods: {
            onNewToken(token) {
                this.tokens.unshift(token);
                this.latestToken = token;
                this.generatingNewToken = false;
            }
        }
    };
</script>

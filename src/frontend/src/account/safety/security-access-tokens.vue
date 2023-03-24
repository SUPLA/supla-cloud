<template>
    <div class="container">
        <loading-cover :loading="accessTokens === undefined">
            <h2>{{ $t('Active web sessions') }}</h2>
            <table class="table table-striped" v-if="accessTokens">
                <thead>
                <tr>
                    <th>{{ $t('Device') }}</th>
                    <th>{{ $t('Expires at') }}</th>
                    <th>{{ $t('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(accessToken, $index) in webappTokens" :key="$index">
                    <td>
                        <span v-if="accessToken.issuerSystem">
                            {{ accessToken.issuerSystem.browserName || $t('Unknown browser') }}
                            {{ accessToken.issuerSystem.browserVersion || '' }}
                            {{ accessToken.issuerSystem.os || '' }}
                            <span v-if="accessToken.issuerSystem.device !== 'Other'">{{ accessToken.issuerSystem.device || '' }}</span>
                        </span>
                        <em v-else>{{ $t('unknown') }}</em>
                    </td>
                    <td>
                        {{ accessToken.expiresAt | formatDateTime }}
                    </td>
                    <td>
                        <a href="" class="btn btn-default btn-xs">
                            <fa icon="sign-out"/>
                            {{ $t('Log out') }}
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
            {{ applicationTokens }}
        </loading-cover>
    </div>
</template>

<script>
    import OauthScopeLabel from "@/account/integrations/oauth-scope-label.vue";

    export default {
        components: {OauthScopeLabel},
        data() {
            return {
                accessTokens: undefined,
            };
        },
        mounted() {
            this.$http.get('access-tokens')
                .then(response => {
                    this.accessTokens = response.body;
                });
        },
        computed: {
            webappTokens() {
                return this.accessTokens?.filter(token => token.isForWebapp);
            },
            applicationTokens() {
                return this.accessTokens?.filter(token => !token.isForWebapp);
            }
        }
    }
</script>

<style scoped>

</style>

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
                <tr v-for="accessToken in webappTokens" :key="accessToken.id">
                    <td>
                        <span v-if="accessToken.issuerSystem">
                            {{ accessToken.issuerSystem.browserName || $t('Unknown browser') }}
                            {{ accessToken.issuerSystem.browserVersion || '' }}
                            {{ accessToken.issuerSystem.os || '' }}
                            <span v-if="accessToken.issuerSystem.device !== 'Other'">{{ accessToken.issuerSystem.device || '' }}</span>
                        </span>
                        <em v-else>{{ $t('unknown') }}</em>
                        <span v-if="accessToken.issuerIp">/ {{ accessToken.issuerIp }}</span>
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
            <h2>{{ $t('Active applications') }}</h2>
            <table class="table table-striped" v-if="accessTokens">
                <thead>
                <tr>
                    <th>{{ $t('Application') }}</th>
                    <th>{{ $t('Expires at') }}</th>
                    <th>{{ $t('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="accessToken in applicationTokens" :key="accessToken.id">
                    <td>
                        {{ accessToken.apiClientAuthorization.apiClient.name }}
                    </td>
                    <td>
                        {{ accessToken.expiresAt | formatDateTime }}
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </loading-cover>
    </div>
</template>

<script>
    export default {
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

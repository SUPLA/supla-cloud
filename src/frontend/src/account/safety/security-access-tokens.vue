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
                        <a @click="accessTokenToDelete = accessToken" class="btn btn-default btn-xs">
                            <fa icon="sign-out"/>
                            {{ $t('Log out') }}
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
            <h2>{{ $t('Active applications') }}</h2>
            <table class="table table-striped" v-if="accessTokens && applicationTokens.length > 0">
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
                        {{ accessToken.apiClient.name }}
                    </td>
                    <td>
                        {{ accessToken.expiresAt | formatDateTime }}
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <empty-list-placeholder v-else-if="accessTokens"/>
        </loading-cover>
        <modal-confirm v-if="accessTokenToDelete"
            class="modal-warning"
            @confirm="deleteAccessToken(accessTokenToDelete)"
            @cancel="accessTokenToDelete = undefined"
            :header="$t('Are you sure you want to log out this device?')"
            :loading="deleting">
            {{ $t('The device that uses this session will loose access to the SUPLA Cloud and will need to reauthenticate.') }}
        </modal-confirm>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";

    export default {
        data() {
            return {
                accessTokens: undefined,
                deleting: false,
                accessTokenToDelete: undefined,
            };
        },
        mounted() {
            this.fetchTokens();
        },
        methods: {
            fetchTokens() {
                this.accessTokens = undefined;
                return this.$http.get('access-tokens')
                    .then(response => {
                        this.accessTokens = response.body;
                    });
            },
            deleteAccessToken(token) {
                this.deleting = true;
                this.$http.delete(`access-tokens/${token.id}`)
                    .then(() => successNotification(this.$t('Success'), this.$t('Selected device has been logged out.')))
                    .then(() => this.fetchTokens())
                    .then(() => this.accessTokenToDelete = undefined)
                    .finally(() => this.deleting = false);
            },
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

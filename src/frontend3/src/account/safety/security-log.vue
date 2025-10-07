<template>
    <div class="container">
        <loading-cover :loading="authAttempts === undefined">
            <table class="table table-striped" v-if="authAttempts">
                <thead>
                <tr>
                    <th>{{ $t('Operation') }}</th>
                    <th>{{ $t('Date and time') }}</th>
                    <th>{{ $t('IP Address') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(logEntry, $index) in authAttempts" :key="$index">
                    <td>
                        <span v-if="logEntry.event.name === 'AUTHENTICATION_SUCCESS'">
                            <fa icon="sign-in" class="text-success" fixed-width/>
                            {{ $t('Authentication successful') }}
                        </span>
                        <span v-if="logEntry.event.name === 'AUTHENTICATION_FAILURE'">
                            <fa icon="sign-in" class="text-danger" fixed-width/>
                            {{ $t('Authentication failure') }}
                        </span>
                        <span v-if="logEntry.event.name === 'PASSWORD_RESET'">
                            <fa icon="key" class="text-warning" fixed-width/>
                            {{ $t('Password reset') }}
                        </span>
                        <span v-if="logEntry.event.name === 'PASSWORD_CHANGED'">
                            <fa icon="key" class="text-info" fixed-width/>
                            {{ $t('Password changed') }}
                        </span>
                    </td>
                    <td>{{ logEntry.createdAt | formatDateTime }}</td>
                    <td>{{ logEntry.ipv4 }}</td>
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
                authAttempts: undefined,
            };
        },
        mounted() {
            this.$http.get('users/current/audit', {params: {events: ['AUTHENTICATION_SUCCESS', 'AUTHENTICATION_FAILURE', 'PASSWORD_RESET', 'PASSWORD_CHANGED']}})
                .then(response => {
                    this.authAttempts = response.body;
                });
        }
    }
</script>

<style scoped>

</style>

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
                            <fa :icon="faSignIn()" class="text-success" fixed-width/>
                            {{ $t('Authentication successful') }}
                        </span>
                        <span v-if="logEntry.event.name === 'AUTHENTICATION_FAILURE'">
                            <fa :icon="faSignIn()" class="text-danger" fixed-width/>
                            {{ $t('Authentication failure') }}
                        </span>
                        <span v-if="logEntry.event.name === 'PASSWORD_RESET'">
                            <fa :icon="faKey()" class="text-warning" fixed-width/>
                            {{ $t('Password reset') }}
                        </span>
                        <span v-if="logEntry.event.name === 'PASSWORD_CHANGED'">
                            <fa :icon="faKey()" class="text-info" fixed-width/>
                            {{ $t('Password changed') }}
                        </span>
                    </td>
                    <td>{{ formatDateTime(logEntry.createdAt) }}</td>
                    <td>{{ logEntry.ipv4 }}</td>
                </tr>
                </tbody>
            </table>
        </loading-cover>
    </div>
</template>

<script>
  import {api} from "@/api/api.js";
  import {formatDateTime} from "@/common/filters-date.js";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import {faKey, faSignIn} from "@fortawesome/free-solid-svg-icons";

  export default {
      components: {LoadingCover},
      methods: {
        faKey() {
          return faKey
        },
        faSignIn() {
          return faSignIn
        }, formatDateTime},
        data() {
            return {
                authAttempts: undefined,
            };
        },
        mounted() {
            api.get('users/current/audit', {params: {events: ['AUTHENTICATION_SUCCESS', 'AUTHENTICATION_FAILURE', 'PASSWORD_RESET', 'PASSWORD_CHANGED']}})
                .then(response => {
                    this.authAttempts = response.body;
                });
        }
    }
</script>

<template>
    <form @submit.prevent="saveNewToken()">
        <div class="form-group">
            <label>{{ $t('Name') }}</label>
            <input type="text"
                v-model="token.name"
                class="form-control"
                :placeholder="$t('What this token is for?')">
        </div>
        <div class="form-group clearfix">
            <label>{{ $t('Scopes') }}</label>
            <p>{{ $t('Scopes define what parts of your account can be accessed when using the token.')}}</p>
            <div class="list-group scope-selector">
                <div class="list-group-item col-xs-12 col-sm-6 col-md-4 col-lg-3"
                    v-for="scope in availableScopes">
                    <h4>{{ $t(scope.label) }}</h4>
                    <toggler v-for="suffix in scope.suffixes"
                        :key="scope.prefix + suffix"
                        :label="suffix"
                        v-model="selectedScopes[scope.prefix + '_' + suffix]"></toggler>
                </div>
            </div>
        </div>
        <div class="form-group text-right">
            <button class="btn btn-green">
                {{ $t('Generate token') }}
            </button>
        </div>
    </form>
</template>

<script>
    export default {
        data() {
            return {
                availableScopes: [
                    {prefix: 'accessids', suffixes: ['r', 'rw'], label: 'Access Identifiers'},
                    {prefix: 'account', suffixes: ['r', 'rw'], label: 'Account'},
                    {prefix: 'channels', suffixes: ['r', 'rw', 'ea'], label: 'Channels'},
                    {prefix: 'channelgroups', suffixes: ['r', 'rw', 'ea'], label: 'Channel groups'},
                    {prefix: 'clientapps', suffixes: ['r', 'rw'], label: 'Client apps'},
                    {prefix: 'iodevices', suffixes: ['r', 'rw'], label: 'IO Devices'},
                    {prefix: 'locations', suffixes: ['r', 'rw'], label: 'Locations'},
                    {prefix: 'schedules', suffixes: ['r', 'rw'], label: 'Schedules'},
                ],
                selectedScopes: {},
                token: {
                    name: '',
                    scopes: []
                }
            };
        },
        mounted() {
        },
        methods: {
            saveNewToken() {
                this.token.scopes = [];
                for (let scope in this.selectedScopes) {
                    if (this.selectedScopes[scope]) {
                        this.token.scopes.push(scope);
                    }
                }
                this.$http.post('integrations/personal-tokens', this.token).then(response => {
                    this.$emit('generated', response.body);
                });
            }
        }
    };
</script>

<style lang="scss">
    .scope-selector {
        .list-group-item {
            border-radius: 0 !important;
        }
    }
</style>

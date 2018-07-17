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
                    <div class="togglers">
                        <div v-for="suffix in scope.suffixes">
                            <toggler :label="scopeSuffixLabels[suffix]"
                                @input="scopeChanged(scope, suffix)"
                                v-model="selectedScopes[scopeId(scope, suffix)]"></toggler>
                        </div>
                    </div>
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
    import {availableScopes, scopeId, scopeSuffixLabels} from "./oauth-scopes";

    export default {
        data() {
            return {
                availableScopes,
                scopeSuffixLabels,
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
            },
            scopeChanged(scope, suffix) {
                const scopeId = this.scopeId(scope, suffix);
                if (suffix == 'r' && !this.selectedScopes[scopeId]) {
                    this.$set(this.selectedScopes, this.scopeId(scope, 'rw'), false);
                } else if (suffix == 'rw' && this.selectedScopes[scopeId]) {
                    this.$set(this.selectedScopes, this.scopeId(scope, 'r'), true);
                }
            },
            scopeId,
        }
    };
</script>

<style lang="scss">
    .scope-selector {
        .list-group-item {
            border-radius: 0 !important;
            h4 {
                margin-top: 0;
            }
            .togglers {
                display: flex;
                justify-content: space-evenly;
            }
        }
    }
</style>

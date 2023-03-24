<template>
    <span>
        <span v-if="scopesLabel">{{ scopesLabel }}</span>
        <em v-else
            class="text-muted">{{ $t('None') }}</em>
    </span>
</template>

<script>
    import {addImplicitScopes, arrayOfScopes, availableScopes, scopeId, scopeSuffixLabels} from "./oauth-scopes";

    export default {
        props: ['scope'],
        computed: {
            scopes() {
                return addImplicitScopes(arrayOfScopes(this.scope));
            },
            scopesLabel() {
                const scopes = this.scopes;
                const existing = [];
                for (let scope of availableScopes) {
                    const existingScopes = [];
                    for (let suffix of scope.suffixes) {
                        if (scopes.indexOf(scopeId(scope, suffix)) !== -1) {
                            existingScopes.push(scopeSuffixLabels[suffix]);
                        }
                    }
                    if (existingScopes.includes('Read') && existingScopes.includes('Modification')) {
                        existingScopes.splice(existingScopes.indexOf('Read'), 1);
                    }
                    if (existingScopes.length) {
                        let allowedActionsLabels = existingScopes.map(label => this.$t(label)).join(', ');
                        existing.push(`${this.$t(scope.label)} (${allowedActionsLabels})`);
                    }
                }
                return existing.join(', ');
            }
        }
    };
</script>

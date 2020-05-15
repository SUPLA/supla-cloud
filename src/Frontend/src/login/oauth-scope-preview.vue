<template>
    <div class="form-group clearfix">
        <div class="list-group scope-selector">
            <div class="list-group-item col-xs-12 col-sm-6"
                v-for="scope in desiredAvailableScopes">
                <h4>{{ $t(scope.label) }}</h4>
                <div class="permissions">
                    <div v-for="suffix in scope.suffixes">
                        <i :class="'pe-7s-' + icons[suffix]"></i>
                        {{ $t(scopeSuffixLabels[suffix]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {addImplicitScopes, arrayOfScopes, availableScopes, scopeId, scopeSuffixLabels} from "../oauth/oauth-scopes";
    import {cloneDeep} from "lodash";

    export default {
        props: ['desiredScopes'],
        data() {
            return {
                desiredAvailableScopes: [],
                scopeSuffixLabels,
                icons: {
                    r: 'look',
                    rw: 'edit',
                    ea: 'power',
                    access: 'moon',
                    webhook: 'call',
                }
            };
        },
        mounted() {
            const desiredScopes = addImplicitScopes(arrayOfScopes(this.desiredScopes));
            const desiredAvailableScopes = cloneDeep(availableScopes);
            desiredAvailableScopes.forEach(
                scope => scope.suffixes = scope.suffixes.filter(suffix => desiredScopes.indexOf(scopeId(scope, suffix)) !== -1)
            );
            this.desiredAvailableScopes = desiredAvailableScopes.filter(scope => scope.suffixes.length > 0);
        },
        methods: {
            scopeId,
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .scope-selector {
        .list-group-item {
            border-radius: 0 !important;
            h4 {
                margin-top: 0;
            }
            &:nth-child(even) {
                border-left: 0;
            }
            &:last-child:nth-child(odd) {
                width: 100%;
            }
            .permissions {
                display: flex;
                justify-content: space-evenly;
                i {
                    display: block;
                    text-align: center;
                    font-size: 2em;
                    margin-bottom: 5px;
                    color: $supla-green;
                }
            }
        }
    }
</style>

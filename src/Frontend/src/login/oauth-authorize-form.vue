<template>
    <div v-title="$t('OAuth authorization')">
        <div class="authorize-form">
            <div class="authorization-logo">
                <span class="app-name">{{ clientName }}</span>
                <span class="arrow"><span class="pe-7s-link"></span></span>
                <img src="assets/img/logo.svg"
                    alt="SUPLA">
            </div>
            <h3 class="text-center">{{ $t('Authorize the application, so that it can access your account.')}}</h3>
            <div class="form-group clearfix">
                <div class="list-group scope-selector">
                    <div class="list-group-item"
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

            <div class="alert alert-info">
                <span class="pe-7s-info"></span>
                {{ $t('After granting the access, you can always remove it by adjusting the settings in your account settings section.') }}
            </div>

            <div class="buttons">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {addImplicitScopes, arrayOfScopes, availableScopes, scopeId, scopeSuffixLabels} from "../oauth/oauth-scopes";
    import {cloneDeep} from "lodash";

    export default {
        components: {ButtonLoadingDots},
        props: ['desiredScopes', 'clientName', 'locale'],
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
            if (this.locale) {
                this.$setLocale(this.locale);
            }
        },
        methods: {
            scopeId,
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .authorize-form {
        width: 90%;
        max-width: 600px;
        margin: 0 auto;
        padding-top: 10px;
        .authorization-logo {
            .app-name {
                font-size: 40px;
                display: inline-block;
                max-width: 55%;
                vertical-align: middle;
            }
            .arrow {
                font-size: 50px;
                font-weight: bold;
                display: inline-block;
                padding: 0 20px;
                vertical-align: middle;
            }
            text-align: center;
            margin-bottom: 40px;
            img {
                width: 150px;
                height: 150px;
            }
        }
        .buttons {
            margin-top: 30px;
        }
        .scope-selector {
            display: flex;
            flex-flow: row wrap;
            > div {
                flex-grow: 1;
                border-radius: 0 !important;
                h4 {
                    margin-top: 0;
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
    }
</style>

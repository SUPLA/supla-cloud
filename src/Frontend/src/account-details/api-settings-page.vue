<template>
    <div :class="'container api-settings-page ' + (animationFinished ? 'animation-finished' : '')">
        <animated-svg id="api-settings-bg"
            :file="'assets/img/api-settings-account-bg.svg' | withBaseUrl"></animated-svg>
        <div class="terminal">
            {{ $t('Welcome Software Developer! RESTfulAPI has been designed having your needs in mind! With this interface you can integrate your own IT solutions with SUPLA.' )}}
        </div>
        <div class="settings">
            <h2>RESTful API</h2>
            <p>{{ $t('OAuth 2.0 Authentication') }}</p>
            <div class="inputs">
                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asfasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            v-clipboard:copy="'blabla'">
                            {{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>Server</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}</button>
                    </span>
                </div>
                <label>Token URL</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>ClientID</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>Secret</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>GrantType</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>Username</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label>Password</label>
            </div>

        </div>
    </div>
</template>

<script type="text/babel">
    import AnimatedSvg from "./animated-svg";
    import Vue from "vue";
    import VueClipboard from 'vue-clipboard2';

    Vue.use(VueClipboard);

    export default {
        components: {
            AnimatedSvg
        },
        data() {
            return {
                user: undefined,
                animationFinished: false,
            };
        },
        mounted() {
            setTimeout(() => this.animationFinished = true, 2000);
            this.$http.get('users/current').then(response => {
                this.user = response.body;
            });
        },
        computed: {}
    };
</script>

<style lang="scss">
    @import '../styles/mixins';
    @import '../styles/variables';

    ._api_settings {
        background-color: $supla-green;
    }

    .api-settings-page {
        $width: 980px;
        #api-settings-bg {
            display: none;
        }
        .terminal {
            color: $supla-green;
            background: $supla-black;
            font-family: Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace;
            font-size: 16px;
            padding: 10px;
        }
        .settings {
            color: $supla-white;
            input {
                background: transparent;
                border: 0;
                padding: 0;
                color: $supla-white;
            }
            label {
                opacity: .9;
                font-weight: normal;
            }
            .input-group {
                border-bottom: 1px solid $supla-white;
                padding-bottom: 3px;
                .btn {
                    border-radius: 5px;
                    text-transform: uppercase;
                }
            }
        }
        @include on-and-up($width) {
            width: $width;
            margin: 0 auto;
            position: relative;
            #api-settings-bg {
                display: block;
            }
            .terminal {
                opacity: 0;
                transition: opacity .5s;
                position: absolute;
                top: 163px;
                left: 549px;
                display: block;
                width: 395px;
                height: 356px;
            }
            .settings {
                position: absolute;
                top: 60px;
                left: 45px;
                width: 481px;
                .inputs {
                    label {
                        margin-top: 5px;
                    }
                }
            }
            &.animation-finished {
                .terminal {
                    opacity: 1;
                }
            }
        }
    }
</style>

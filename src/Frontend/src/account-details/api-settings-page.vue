<template>
    <div :class="'api-settings-page ' + (animationFinished ? 'animation-finished' : '')">
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
                            data-id="server">{{ $t('copy') }}
                        </button>
                    </span>
                </div>
                <label for="server">Server</label>

                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        id="token-url"
                        value="asdasdf"
                        readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default copy"
                            type="button"
                            data-id="token-url">{{ $t('copy') }}</button>
                    </span>
                </div>
                <label for="token-url">Token URL</label>
            </div>

        </div>
    </div>
</template>

<script type="text/babel">
    import AnimatedSvg from "./animated-svg";
    import TimezonePicker from "./timezone-picker";
    import AccountPasswordChangeModal from "./account-password-change-modal";

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
            }
            .input-group {
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
                top: 169px;
                left: 551px;
                display: block;
                width: 407px;
                height: 366px;
            }
            .settings {
                position: absolute;
                top: 60px;
                left: 45px;
                width: 481px;
            }
            &.animation-finished {
                .terminal {
                    opacity: 1;
                }
            }
        }
    }
</style>

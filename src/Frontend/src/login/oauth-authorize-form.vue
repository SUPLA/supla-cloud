<template>
    <div v-title="$t('Login')">

        <div class="authorize-form">
            <div class="authorization-logo">
                <span class="app-name">SUPLA Scripts</span>
                <span class="arrow"><span class="pe-7s-link"></span></span>
                <img src="assets/img/logo.svg"
                    alt="SUPLA">
            </div>
            <h3 class="text-center"
                style="margin-bottom: 15px">Aplikacja <strong>SUPLA Scripts</strong> prosi o możliwość:</h3>
            <h4>Odczytu</h4>
            <ul>
                <li>szczegółów Twojego konta, w tym <strong>Twojego adresu e-mail oraz strefy czasowej</strong></li>
                <li>listy i szczegółów <strong>Twoich urządzeń</strong></li>
                <li>listy i szczegółów <strong>Twoich kanałów</strong></li>
            </ul>
            <h4>Modyfikacji</h4>
            <ul>
                <li>ustawień <strong>Twoich kanałów</strong></li>
            </ul>
            <h4>A także</h4>
            <ul>
                <li class="text-danger"><strong>sterowania Twoimi kanałami</strong></li>
                <li class="text-danger"><strong>sterowania Twoimi grupami kanałami</strong></li>
                <li>dostępu do Twojego konta nawet gdy nie ma Cię przy komputerze</li>
            </ul>

            <div class="alert alert-info">
                <span class="pe-7s-info"></span>
                Po przyznaniu dostępu <strong>możesz zawsze zmienić swoją decyzję</strong> w ustawieniach Twojego konta w SUPLA Cloud.
            </div>

            <div class="buttons">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import LoginFooter from "./login-footer.vue";
    import {errorNotification} from "../common/notifier";

    export default {
        components: {ButtonLoadingDots, LoginFooter},
        data() {
            return {
                authenticating: false,
                username: '',
                password: '',
                displayError: false,
            };
        },
        methods: {
            login() {
                if (!this.authenticating) {
                    this.authenticating = true;
                    this.displayError = false;
                    this.$user.authenticate(this.username, this.password)
                        .then(() => this.$router.push('/'))
                        .catch((error) => {
                            if (error.status == 401) {
                                this.displayError = true;
                                // TODO else if blocked
                            } else {
                                errorNotification(this.$t('Information'), this.$t('Sign in temporarily unavailable. Please try again later.'));
                            }
                            this.authenticating = false;
                        });
                }
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    body._auth_login {
        background: $supla-white;
    }

    .authorize-form {
        $height: 500px;
        width: 90%;
        max-width: 600px;
        height: $height;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -$height/2;
        margin-left: -300px;
        @include on-and-down(500px) {
            position: static;
            margin: 10px auto;
            height: auto;
        }
        @media (max-height: $height) {
            position: static;
            margin: 10px auto;
        }
        .authorization-logo {
            .app-name {
                font-size: 40px;
            }
            .arrow {
                font-size: 50px;
                font-weight: bold;
                display: inline-block;
                padding: 0 20px;
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
        form {
            .input-group-addon > span {
                font-size: 2em;
            }
        }
        input[type=text], input[type=password], input[type=email] {
            &.form-control {
                border: 0;
                border-radius: 0;
                border-bottom: 1px solid $supla-grey-light;
                box-shadow: initial;
                background: transparent;
                padding-left: 0;
                padding-right: 0;
            }
        }
        .additional-buttons {
            display: flex;
            justify-content: space-between;
            > .btn {
                flex: 1;
                max-width: 48%;
                display: flex;
                justify-content: center;
                align-items: center;
                img {
                    float: left;
                    height: 23px;
                    margin-right: 5px;
                }
            }
        }
        .error {
            display: inline-block;
            background: $supla-yellow;
            padding: 12px 20px;
            margin-top: 15px;
            border-radius: 3px;
            color: $supla-black;
            margin-bottom: 20px;
            &.locked {
                background: $supla-red;
                color: $supla-white;
            }
        }
    }
</style>

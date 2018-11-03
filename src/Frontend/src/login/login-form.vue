<template>
    <div class="login-form">
        <div class="logo">
            <img src="assets/img/logo.svg"
                alt="SUPLA">
        </div>

        <div class="form-group form-group-lg">
            <span class="input-group">
                <span class="input-group-addon">
                    <span class="pe-7s-user"></span>
                </span>
                <input type="email"
                    required
                    autocorrect="off"
                    autocapitalize="none"
                    :placeholder="$t('Your email')"
                    v-model="username"
                    name="_username"
                    class="form-control">
            </span>
        </div>
        <div class="form-group form-group-lg login-password">
            <span class="input-group">
                <span class="input-group-addon">
                    <span class="pe-7s-lock"></span>
                </span>
                <input type="password"
                    :placeholder="$t('Password')"
                    name="_password"
                    v-model="password"
                    class="form-control">
            </span>
        </div>
        <div>
            <slot></slot>
        </div>
        <div class="form-group text-right">
            <button type="submit"
                class="btn btn-green btn-lg"
                @click="$emit('input', {username: username, password: password})"
                :disabled="authenticating">
                <span v-if="!authenticating">
                    {{ $t(buttonText) }}
                </span>
                <button-loading-dots v-else></button-loading-dots>
            </button>
        </div>
        <transition name="fade">
            <div class="error locked"
                v-if="error === 'locked'">
                <strong>{{ $t('Your account has been locked.') }}</strong>
                {{ $t('Please wait a moment before the next login attempt.') }}
            </div>
        </transition>
        <transition name="fade">
            <router-link to="/forgotten-password"
                class="error"
                v-if="error == 'error'">
                <strong>{{ $t('Forgot your password?') }}</strong>
                {{ $t('Don\'t worry, you can always reset your password via email. Click here to do so.') }}
            </router-link>
        </transition>
        <div v-if="!oauth" class="additional-buttons form-group">
            <router-link to="/devices"
                class="btn btn-white btn-wrapped">
                <img src="assets/img/devices.png">
                {{ $t('Supla for devices') }}
            </router-link>
            <a class="btn btn-white btn-wrapped"
                href="/auth/create">
                <img src="/assets/img/user.png">
                {{ $t('Create an account') }}
            </a>
        </div>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    export default {
        props: ['authenticating', 'oauth', 'error', 'value', 'intitialUsername', 'submitButtonText'],
        components: {ButtonLoadingDots},
        data() {
            return {
                username: '',
                password: '',
                buttonText: '',
            };
        },
        mounted() {
            this.username = this.intitialUsername || '';
            this.buttonText = this.submitButtonText || 'Sign In';
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .login-form {
        $height: 500px;
        width: 90%;
        max-width: 400px;
        height: $height;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -$height/2;
        margin-left: -200px;
        @include on-and-down(500px) {
            position: static;
            margin: 10px auto;
            height: auto;
        }
        @media (max-height: $height) {
            position: static;
            margin: 10px auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
            img {
                width: 150px;
                height: 150px;
            }
        }
        .input-group-addon > span {
            font-size: 2em;
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

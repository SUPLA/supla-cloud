<template>
    <div>
        <transition name="fade-router">
            <whole-screen-message v-if="registeredEmail"
                icon="pe-7s-mail-open-file"
                header-i18n="Check your email inbox"
                :message="registeredEmail">
                <p>
                    <resend-account-activation-link :username="registeredEmail"
                        :notifications="true"></resend-account-activation-link>
                </p>
            </whole-screen-message>
        </transition>
        <transition name="fade-router">
            <div v-if="!registeredEmail">
                <div class="register-page">
                    <div class="register-slider-container">
                        <register-slider :texts="['register-slide1', 'register-slide2', 'register-slide3']"></register-slider>
                    </div>
                    <div class="create-form-container">
                        <register-form @registered="registeredEmail = $event"></register-form>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    import RegisterSlider from './register-slider';
    import RegisterForm from './register-form';
    import WholeScreenMessage from "./whole-screen-message";
    import ResendAccountActivationLink from "./resend-account-activation-link";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {ResendAccountActivationLink, WholeScreenMessage, RegisterSlider, RegisterForm},
        data() {
            return {
                registeredEmail: ''
            };
        },
        mounted() {
            if (!this.frontendConfig.accountsRegistrationEnabled) {
                this.$router.push('/');
            }
        },
        computed: {
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
        }
    };
</script>

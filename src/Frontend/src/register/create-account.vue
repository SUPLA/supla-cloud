<template>
    <div>
        <transition name="fade-router">
            <check-email v-if="registeredEmail"
                :email="registeredEmail"></check-email>
        </transition>
        <transition name="fade-router">
            <div v-if="!registeredEmail">
                <register-slider></register-slider>
                <register-form @registered="registeredEmail = $event"></register-form>
            </div>
        </transition>
        <login-footer class="register-footer"></login-footer>
    </div>
</template>

<script>
    import RegisterSlider from './register-slider';
    import RegisterForm from './register-form';
    import CheckEmail from './check-email';
    import LoginFooter from "../login/login-footer";

    export default {
        components: {LoginFooter, CheckEmail, RegisterSlider, RegisterForm},
        data() {
            return {
                registeredEmail: ''
            };
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables.scss";

    .register-footer {
        &, & a, & select {
            color: $supla-white !important;
        }
    }
</style>

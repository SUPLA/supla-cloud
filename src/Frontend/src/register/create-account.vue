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
        <register-footer></register-footer>
    </div>
</template>

<script>
    import RegisterSlider from './register-slider';
    import RegisterForm from './register-form';
    import RegisterFooter from './register-footer';
    import CheckEmail from './check-email';

    export default {
        components: {CheckEmail, RegisterSlider, RegisterForm, RegisterFooter},
        data() {
            return {
                registeredEmail: ''
            };
        }
    };
</script>

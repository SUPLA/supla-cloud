<template>
    <div>
        <div class="bg"></div>
        <check-email v-if="registered"></check-email>
        <div v-else>
            <register-slider></register-slider>
            <register-form @registered="registered = true"></register-form>
        </div>
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
                registered: false
            };
        }
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    .bg {
        background: $supla-green;
        color: $supla-white;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -5;
    }
</style>

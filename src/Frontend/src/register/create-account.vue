<template>
<div>
    <div class="bg"></div>

    <register-slider></register-slider>
    <register-form></register-form>
    <register-footer></register-footer>
</div>
</template>
<script>
import RegisterSlider from './register-slider.vue';
import RegisterForm from './register-form.vue';
import RegisterFooter from './register-footer.vue';

export default {
    components: { RegisterSlider, RegisterForm, RegisterFooter },

    mounted() {
        document.title = this.$t( 'Create an account' ) + ' | ' + 'SUPLA CLOUD';
    }
};
</script>
<style lang="scss">

.bg {
    background: #00D151;
    color: #fff;
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: -5;
}

</style>

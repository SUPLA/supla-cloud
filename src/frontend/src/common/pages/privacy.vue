<template>
    <div class="terms-page">
        <div class="wrapper">
            <regulations-modal></regulations-modal>
        </div>
    </div>
</template>

<script>
    import RegulationsModal from "../errors/privacy-modal";

    export default {
        props: ['value'],
        components: {RegulationsModal},
        data() {
            return {
                rulesShown: false
            };
        },
        methods: {
            goToLoginPage() {
                this.$router.push({name: 'login'});
            },
        },
    };
</script>


<template>
    <div class="terms-page">
        <div class="wrapper">
            <regulations-modal v-if="!rulesShown"
                @confirm="goToLoginPage()"></regulations-modal>
        </div>
    </div>
</template>

<script>
  import RegulationsModal from "../errors/regulations-modal.vue";

  export default {
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


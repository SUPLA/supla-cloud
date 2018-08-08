<template>
    <form @submit="authenticating = true"
        action="/oauth/v2/auth_login"
        ref="loginForm"
        method="post"
        v-title="$t('Login')"
        class="login-oauth-form">
        <login-form :authenticating="authenticating"
            :error="error"
            :intitial-username="lastUsername"></login-form>
        <login-footer remind-password-link="true"></login-footer>
    </form>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import LoginForm from "./login-form";

    export default {
        props: ['lastUsername', 'error'],
        components: {LoginForm, LoginFooter},
        data() {
            return {
                authenticating: false,
                displayError: false,
            };
        }
    };
</script>

<style lang="scss">

</style>

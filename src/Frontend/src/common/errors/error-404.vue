<template>
    <div>
        <div class="container text-center">
            <h1 class="nocapitalize">{{ $t('Are you lost?') }}</h1>
            <i class="pe-7s-way"
                style="font-size: 160px"></i>
            <h5>{{ $t('The server has returned an HTTP 404 Error. It means that the item you are looking for does not exist.') }}</h5>
        </div>
        <login-footer v-if="!$user.username"></login-footer>
    </div>
</template>

<script>
    import LoginFooter from "../../login/login-footer";

    export default {components: {LoginFooter}};
</script>

<template>
    <div>
        <div class="container text-center">
            <h1 class="nocapitalize">{{ $t(header) }}</h1>
            <i :class="icon"
                style="font-size: 160px"></i>
            <h5>{{ $t(message) }}</h5>
            <slot></slot>
        </div>
        <login-footer v-if="!$user.username"></login-footer>
    </div>
</template>

<script>
    import LoginFooter from "../../login/login-footer";

    export default {
        props: ['header', 'message', 'icon'],
        components: {LoginFooter}
    };
</script>

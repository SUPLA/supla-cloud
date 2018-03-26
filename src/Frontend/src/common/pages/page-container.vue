<template>
    <div>
        <error-403 v-if="error == 403"></error-403>
        <error-404 v-else-if="error == 404"></error-404>
        <slot v-else></slot>
    </div>
</template>

<script>
    import Error403 from "../errors/error-403";
    import Error404 from "../errors/error-404";

    export default {
        props: ['error'],
        components: {Error403, Error404},
        watch: {
            error() {
                if (this.error) {
                    document.body.setAttribute('class', 'red');
                } else {
                    document.body.removeAttribute('class');
                }
            }
        }
    };
</script>

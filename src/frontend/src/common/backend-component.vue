<template>
    <div>
        <component v-if="component"
            :is="component.name"
            v-bind="component.props"></component>
        <error-page v-else-if="error"
            header-i18n="Are you lost?"
            message-i18n="The server has returned an HTTP 404 Error. It means that the item you are looking for does not exist."
            icon="pe-7s-way"></error-page>
    </div>
</template>

<script>
    import DirectLinkExecutionResult from "../direct-links/result-page/direct-link-execution-result";
    import ErrorPage from "./errors/error-page";

    export default {
        components: {ErrorPage, DirectLinkExecutionResult},
        data() {
            return {
                component: undefined,
                error: false,
            }
        },
        mounted() {
            this.component = window.component;
            this.error = !this.component;
        }
    }
</script>


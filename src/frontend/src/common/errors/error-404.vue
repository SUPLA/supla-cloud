<template>
    <error-page header-i18n="Are you lost?"
        message-i18n="The server has returned an HTTP 404 Error. It means that the item you are looking for does not exist."
        icon="pe-7s-way"></error-page>
</template>

<script>
    import ErrorPage from "./error-page";

    export default {components: {ErrorPage}};
</script>

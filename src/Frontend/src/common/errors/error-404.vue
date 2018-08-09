<template>
    <error-page header="Are you lost?"
        message="The server has returned an HTTP 404 Error. It means that the item you are looking for does not exist."
        icon="pe-7s-way"></error-page>
</template>

<script>
    import ErrorPage from "./error-page";

    export default {components: {ErrorPage}};
</script>

<template>
    <div class="container text-center">
        <h1 class="nocapitalize">{{ $t('Seems like a secret place') }}</h1>
        <i class="pe-7s-door-lock error-page-icon"
            style="font-size: 160px"></i>
        <h5>{{ $t('The server has returned an HTTP 403 Error. It means that you are not authorized to display the item you are looking for.') }}</h5>
    </div>
</template>

<script>
    export default {};
</script>

<template>
  <ErrorPage :header-i18n="header" :message-i18n="message" :icon="icon" :status-code="statusCode"/>
</template>

<script setup>
  import ErrorPage from "./error-page.vue";

  const props = defineProps({statusCode: Number});

  const statusCode = window.errorDetails?.statusCode || props.statusCode || 404;

  const header = {
    400: 'Invalid request', // i18n
    403: 'Seems like a secret place', // i18n
    404: 'Are you lost?', // i18n
    429: 'We try to be faster', // i18n
    430: 'You have exceeded your API Rate Limit', // i18n
  }[statusCode] || 'Error'; // i18n

  const message = {
    400: 'The server has returned an HTTP 400 Error. It means that the request you tried to perform is invalid.', // i18n
    403: 'The server has returned an HTTP 403 Error. It means that you are not authorized to display the item you are looking for.', // i18n
    404: 'The server has returned an HTTP 404 Error. It means that the item you are looking for does not exist.', // i18n
    429: 'However, currently there is too heavy load on our servers. We are monitoring the situation. Come back in a while.', // i18n
    430: 'Wait a while before the next request. You can check your limits in the account settings.', // i18n
  }[statusCode] || 'The server has returned an HTTP {statusCode} error.'; // i18n

  const icon = {
    400: 'pe-7s-shuffle',
    403: 'pe-7s-door-lock',
    404: 'pe-7s-way',
    429: 'pe-7s-bicycle',
    430: 'pe-7s-bicycle',
  }[statusCode] || 'pe-7s-gleam';
</script>

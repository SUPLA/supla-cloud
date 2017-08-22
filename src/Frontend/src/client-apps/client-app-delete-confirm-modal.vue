<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Are you sure you want to delete this client?')">
        <p>{{ $t('Deleting the client will automatically log it out.') }}</p>
        <p>{{ $t('Further registration will be required to use this device again.') }}</p>
    </modal-confirm>
</template>

<script>
    export default {
    };
</script>

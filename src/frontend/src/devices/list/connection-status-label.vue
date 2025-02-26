<template>
    <span>
        <span class="label label-warning mr-2"
            v-if="model?.state?.connectedCode === 'CONNECTED_NOT_AVAILABLE'">{{ $t('Not available') }}</span>
        <span class="label label-info mr-2" v-if="model?.state?.connectedCode === 'FIRMWARE_UPDATE_ONGOING'">{{ $t('Updating...') }}</span>
        <span class="label label-warning mr-2" v-if="model?.state?.connectedCode === 'OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED'">
            <fa icon="exclamation-triangle"/>
        </span>
        <span :class="'label ' + (model.connected ? 'label-success' : 'label-danger')" v-if="model && model.connected !== undefined">
            {{ model.connected ? $t('Connected') : $t('Disconnected') }}
        </span>
    </span>
</template>

<script setup>
    defineProps({
        model: Object,
    })
</script>

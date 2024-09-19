<template>
    <span :class="'label ' + (model.connected ? 'label-success' : 'label-danger')" v-if="model && model.connected !== undefined">
        {{ model.connected ? $t('Connected') : $t('Disconnected') }}
    </span>
</template>

<script setup>
    defineProps({
        model: Object,
    })
</script>

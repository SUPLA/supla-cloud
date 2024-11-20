<template>
    <span>
        <span class="label label-warning mr-2" v-if="model && model.operational === false">{{ $t('Not available') }}</span>
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

<template>
    <span :class="'label ' + (app.connected ? 'label-success' : 'label-grey')"
        v-if="app.connected !== undefined">
        {{ $t(app.connected ? 'Active' : 'Idle') }}
    </span>
</template>

<script setup>
    defineProps({app: Object});
</script>

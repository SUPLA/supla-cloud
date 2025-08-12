<template>
    <span>{{ relativeLabel }}</span>
</template>

<script setup>
    import {toLuxon} from "@/common/filters-date";
    import {useIntervalFn} from "@vueuse/core";
    import {ref} from "vue";

    const props = defineProps({datetime: [Object, String], pattern: {type: String, default: '%s'}});

    const relativeLabel = ref('');

    function formatDateTimeRelative() {
        if (!props.datetime) {
            return '';
        }
        return props.pattern.replace('%s', toLuxon(props.datetime).toRelative());
    }

    useIntervalFn(() => relativeLabel.value = formatDateTimeRelative(), 30000, {immediateCallback: true});
</script>

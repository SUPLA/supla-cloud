<template>
    <span class="fa-li">
        <fa v-if="value" icon="fa-solid fa-check-square"/>
        <fa v-else icon="fa-regular fa-square"/>
    </span>
</template>

<script>
    export default {
        props: {
            value: Boolean,
        }
    };
</script>

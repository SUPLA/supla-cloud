<template>
    <span>
        {{ $t('Need one of these?') }}
        <a v-for="symbol in symbols"
            :key="symbol"
            class="symbol"
            @click="$emit('typed', symbol)">{{ symbol }}</a>
    </span>
</template>

<script>
    export default {
        data() {
            return {
                symbols: ['μ', '²', '³', '°', '€', '£'],
            };
        }
    };
</script>

<style scoped>
    .symbol {
        display: inline-block;
        padding: 0 3px;
        font-size: 1.3em;
    }
</style>

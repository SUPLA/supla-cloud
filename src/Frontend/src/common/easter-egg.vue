<template>
    <img class="easter-egg"
        :src="`assets/img/easter-eggs/egg${number}.svg`"
        alt="">
</template>

<script>
    export default {
        data() {
            return {
                number: Math.floor(Math.random() * 3) + 1,
            };
        }
    };
</script>

<style>
    .easter-egg {
        height: 2em;
        margin: 5px;
    }
</style>

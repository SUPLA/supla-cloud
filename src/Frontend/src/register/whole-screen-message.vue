<template>
    <div class="message">
        <i :class="icon"></i>
        <h1>{{ $t(header)}}</h1>
        <p v-if="message">{{ message }}</p>
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: ['icon', 'header', 'message'],
    };
</script>

<style lang="scss"
    scoped>
    .message {
        padding-top: 5%;
        text-align: center;

        i {
            font-size: 200px;
            display: inline-block;
            margin-bottom: 40px;
        }

        p {
            font-size: 17px;
            font-weight: 300;
            margin-top: 10px;
            opacity: .9;
        }
    }
</style>

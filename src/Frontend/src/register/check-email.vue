<template>
    <div class="message">
        <i class="pe-7s-mail-open-file"></i>
        <h1>{{ $t('Sprawdź swoją skrzynkę email')}}</h1>
        <p>{{ email }}</p>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                email: this.$store.state.createdUser
            };
        },
        mounted() {
            document.title = this.$t('Check your email box') + ' | ' + 'SUPLA CLOUD';
        }
    };
</script>

<style lang="scss"
    scoped>
    .message {
        color: white;
        padding-top: 5%;
        text-align: center;

        i {
            font-size: 200px;
            display: inline-block;
            margin-bottom: 40px;
        }

        p {
            color: #fff;
            font-size: 17px;
            font-weight: 300;
            display: inline-block;
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.8);
        }
    }
</style>

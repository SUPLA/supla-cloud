<template>
    <div>
        <div class="bg"></div>

        <div class="message">
            <i class="pe-7s-mail-open-file"></i>
            <h1>{{ $t('Sprawdź swoją skrzynkę email')}}</h1>
            <p>{{ email }}</p>
        </div>

        <footer>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <a class="brand nav-link"
                        href="https://www.supla.org">www.supla.org</a>
                </div>
            </div>
        </footer>
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

<style lang="scss">
    .bg {
        background: #00d151;
        color: #fff;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -5;
    }

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

    footer {
        margin: 10px;
        color: #fff;
        font-size: 13px;
        line-height: 32px;

        @media screen and (max-width: 899px) {
            position: static;
        }

        @media screen and (min-width: 900px) {
            padding: 0 20px;
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: 9999;
            width: calc(100% - 20px);
        }

        a {
            border: solid 1px transparent;
            border-radius: 3px;
            color: #fff;
            font-weight: 400;
            padding: 7px 9px;

            &:hover {
                background: none;
                border-color: rgba(255, 255, 255, 0.5);
                color: #fff;
            }
        }
    }
</style>

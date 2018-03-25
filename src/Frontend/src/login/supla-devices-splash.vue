<template>
    <div>
        <img class="app-preview"
            src="/assets/img/supla-app-preview.png">
        <div class="container supla-devices-preview">
            <h1 class="text-center">{{ $t('Get SUPLA on your devices') }}</h1>
            <p class="text-center">{{ $t('...and start your adventure with building automation today!') }}</p>
            <div class="row app-links">
                <div class="col-xs-12">
                    <h4>{{ $t('Mobile Apps') }}</h4>
                </div>
                <div class="col-sm-3">
                    <a href="https://itunes.apple.com/us/app/supla/id996384706">
                        <img src="/assets/img/apple.svg">iOS (iPhone)
                    </a>
                </div>
                <div class="col-sm-3">
                    <a>
                        <div class="coming-soon"><span>{{ $t('Coming Soon') }}</span></div>
                        <img src="/assets/img/apple.svg">iOS (iPad)
                    </a>
                </div>
                <div class="col-sm-3">
                    <a href="https://play.google.com/store/apps/details?id=org.supla.android">
                        <img src="assets/img/android.svg">Android
                    </a>
                </div>
                <div class="col-sm-3">
                    <a>
                        <div class="coming-soon"><span>{{ $t('Coming Soon') }}</span></div>
                        <img src="assets/img/windows.svg">Windows Phone
                    </a>
                </div>
                <div class="col-xs-12">
                    <h4>{{ $t('I/O Devices') }}</h4>
                </div>
                <div class="col-sm-3">
                    <a href="https://www.supla.org/download">
                        <img src="assets/img/raspberrypi.svg">Raspberry
                    </a>
                </div>
                <div class="col-sm-3">
                    <a href="https://www.supla.org/download">
                        <img src="assets/img/esp8266.svg">ESP8266
                    </a>
                </div>
                <div class="col-sm-3">
                    <a href="https://www.supla.org/download">
                        <img src="assets/img/arduino.svg">Arduino
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <router-link to="/"
                        class="back">
                        <i class="pe-7s-close-circle"></i>
                    </router-link>
                </div>
            </div>
        </div>
        <div class="github">
            <a href="https://github.com/SUPLA">
                <div class="col-xs-3">
                    <img src="assets/img/github.svg">
                </div>
                <div class="col-xs-9">
                    {{ $t('Sources available from') }} <strong>GitHub</strong>
                </div>
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            document.title = this.$t( 'Supla for devices' ) + ' | ' + 'SUPLA CLOUD';
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .supla-devices-preview {
        padding-top: 110px;
        max-width: 800px;
        @media (max-width: 1100px) {
            padding-top: 10px;
        }
        .app-links {
            text-align: center;
            a {
                display: inline-block;
                width: 186px;
                height: 154px;
                padding: 10px 12px;
                border: solid 2px $supla-green;
                border-radius: 5px;
                text-align: center;
                position: relative;
                margin-bottom: 10px;
                &:hover {
                    color: $supla-green;
                    background: rgba(41, 175, 36, 0.08)
                }
                &[href]:active {
                    transform: scale(0.95);
                }
                img {
                    display: block;
                    max-width: 90px;
                    max-height: 90px;
                    margin: 5px auto 10px;
                }
                .coming-soon {
                    position: absolute;
                    background: rgba(255, 255, 255, 0.6);
                    top: 0;
                    left: 0;
                    width: 182px;
                    height: 150px;
                    border-radius: 5px;
                    overflow: hidden;
                    span {
                        position: absolute;
                        padding: 5px;
                        font-weight: 600;
                        text-transform: uppercase;
                        text-align: center;
                        background: $supla-yellow;
                        color: $supla-black;
                        transform: rotate(39deg);
                        width: 300px;
                        left: -58px;
                        top: 60px;
                    }
                }
            }
        }
        .back {
            display: inline-block;
            padding-top: 15px;
            font-size: 40px;
        }
    }

    .github {

        text-align: center;
        a {
            display: inline-block;
            background: $supla-black;
            width: 350px;
            border-radius: 3px;
            color: white;
            margin: 0 auto;
            padding: 7px;
            line-height: 40px;
            img {
                max-height: 40px;
            }
        }
    }

    .app-preview {
        max-width: 400px;
        position: absolute;
        display: block;
        top: 0;
        right: 0;
        @media (max-width: 1100px) {
            display: none;
        }
    }
</style>

<template>
    <div class="container home-page">
        <h1 v-title>{{ $t('Start Here') }}</h1>
        <p class="subinfo">{{ $t('WELCOME_MESSAGE') }}</p>
        <div class="row">
            <div class="col-md-6 step">
                <div class="roof">
                    <img :src="'/assets/img/step-1.svg' | withBaseUrl"
                        alt="">
                </div>
                <div class="basement">
                    <h2>supla-dev</h2>
                    <p>{{ $t('Enter the data below in the settings of your control device (I/O Device).') }}</p>
                    <input type="text"
                        v-model="address"
                        readonly>
                    <label>{{ $t('Server address') }}</label>
                    <loading-cover :loading="loading">
                        <div v-if="location">
                            <input type="text"
                                v-model="location.id"
                                readonly>
                            <label>{{ $t('Location Identifier') }}</label>
                            <input type="text"
                                v-model="location.password"
                                readonly>
                            <label>{{ $t('Password') }}</label>
                        </div>
                    </loading-cover>
                </div>
            </div>
            <div class="col-md-6 step">
                <div class="roof">
                    <img src="/assets/img/step-2.svg"
                        alt="">
                </div>
                <div class="basement">
                    <h2>supla-client</h2>
                    <p>{{ $t('Enter the data below in your mobile SUPLA application on your smartphone.') }}</p>
                    <input type="text"
                        v-model="address"
                        readonly>
                    <label>{{ $t('Server address') }}</label>
                    <loading-cover :loading="loading">
                        <div v-if="accessId">
                            <input type="text"
                                v-model="accessId.id"
                                readonly>
                            <label>{{ $t('Access Identifier') }}</label>
                            <input type="text"
                                v-model="accessId.password"
                                readonly>
                            <label
                                class="password">{{ $t('Password') }}</label>
                        </div>
                    </loading-cover>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        data() {
            return {
                location: undefined,
                accessId: undefined,
                address: '',
                loading: true,
            };
        },
        mounted() {
            this.$http.get('locations?include=accessids,password').then(response => {
                const enabledLocations = response.body.filter(location => location.enabled);
                for (let location of enabledLocations) {
                    const accessIds = location.accessIds.filter(aid => aid.enabled);
                    if (accessIds.length) {
                        this.location = location;
                        this.accessId = accessIds[0];
                        break;
                    }
                }
                if (!this.location && enabledLocations.length) {
                    this.location = enabledLocations[0];
                }
            }).finally(() => this.loading = false);
            this.$http.get('server-info').then(response => this.address = response.body.address);
        }
    };
</script>

<style lang="scss">
    @import '../styles/mixins';
    @import '../styles/variables';

    .home-page {
        @include on-and-up(992px) { margin-top: 50px; }
        h1 {
            font-size: 72px;
            line-height: 72px;
            letter-spacing: -6px;
            margin-bottom: 25px
        }
        .subinfo {
            white-space: pre-line;
        }
        .step {
            margin-top: 20px;
            .roof {
                height: 110px;
                overflow: hidden;
            }
            .basement {
                $bottom-radius: 15px;
                background: $supla-yellow;
                padding: 20px;
                padding-top: 0;
                border-bottom-left-radius: $bottom-radius;
                border-bottom-right-radius: $bottom-radius;
                h2 {
                    margin-top: -35px;
                    @include on-and-up(500px) { margin-top: -30px; }
                    @include on-and-up(560px) { margin-top: -25px; }
                    @include on-and-up(600px) { margin-top: -20px; }
                    @include on-and-up(670px) { margin-top: 0; }
                    @include on-and-up(992px) { margin-top: -30px; }
                    @include on-and-up(1200px) { margin-top: -20px; }
                    text-align: center;
                }
                input[type="text"] {
                    background: transparent;
                    border: none;
                    border-bottom: 1px solid rgba(0, 2, 4, .25);
                    width: calc(100% + 40px);
                    line-height: 36px;
                    padding: 0 20px;
                    margin-left: -20px;
                    margin-top: 8px;
                    font-size: 16px;
                }
                label {
                    font-size: 13px;
                    font-weight: 400;
                    color: rgba(0, 2, 4, .6);
                    line-height: 26px;
                }
            }
            &:last-child {
                .basement {
                    background: $supla-green;
                    color: white;
                    input[type=text] {
                        border-color: rgba(255, 255, 255, .8);
                    }
                    label {
                        color: rgba(255, 255, 255, .9);
                    }
                }
            }
        }
    }
</style>

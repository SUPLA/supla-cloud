<template>
    <div class="container" v-if="device.locked">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <h2 class="no-margin-top">{{ $t('The device is locked') }}</h2>

                <p>{{ $t('Locked devices are meant to be distributed and installed only by the authorized installers.') }}</p>
                <p>{{ $t('If you are not an authorized installer of this device, you should contact the seller and request a refund.') }}</p>
                <form @submit.prevent="sendUnlockRequest()">
                    <div class="form-group form-group-lg">
                        <label for>{{ $t('Installer e-mail address') }}</label>
                        <input type="email" class="form-control form-control-lg text-center" v-model="email" required>
                    </div>
                    <div class="form-group form-group-lg">
                        <label for>{{ $t('Unlock code') }}</label>
                        <input type="text" class="form-control form-control-lg text-center text-monospace" v-model="code" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-green" :disabled="unlocking">
                            <button-loading-dots v-if="unlocking"/>
                            <span v-else>
                                <fa icon="unlock"/>
                                {{ $t('Unlock the device') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import 'vue-slider-component/theme/antd.css';

    export default {
        props: {
            device: Object,
        },
        data() {
            return {
                code: '',
                email: '',
                unlocking: false,
            };
        },
        methods: {
            sendUnlockRequest() {
                this.unlocking = true;
                this.$http
                    .patch(`iodevices/${this.device.id}`, {action: 'unlock', code: this.code, email: this.email})
                    .then(() => this.$router.replace({name: 'device.channels', params: {id: this.device.id}}))
                    .then(() => this.$router.go())
                    .finally(() => this.unlocking = false);
            }
        }
    }
</script>

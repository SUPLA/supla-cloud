<template>
    <div class="channel-params-integrations-settings">
        <h4 class="text-center">{{ $t("Integrations settings") }}</h4>
        <div class="btn-group d-flex align-items-center justify-content-center">
            <a class="btn btn-default"
                @click="$emit('input', time)">
                Alexa<sup>&reg;</sup>
            </a>
            <a class="btn btn-default" @click="openGoogleSettings()">
                Google Home<sup>&reg;</sup>
            </a>
        </div>
        <modal v-if="googleSettings" class="modal-450"
            :header="$t('{serviceName} integration', {serviceName: 'Google Home®'})"
            @confirm="updateGoogleSettings()"
            :cancellable="true"
            @cancel="googleSettings = undefined">
            <dl>
                <dd>{{ $t('Available') }}</dd>
                <dt class="text-center">
                    <toggler v-model="googleEnabled"></toggler>
                </dt>
            </dl>
            <dl v-if="googleEnabled">
                <dd>{{ $t('Action confirmation') }}</dd>
                <dt class="text-center">
                    <div class="btn-group d-flex align-items-center justify-content-center">
                        <a :class="['btn', {'btn-default': googleActionConfirmation !== 'none', 'btn-green': googleActionConfirmation === 'none'}]"
                            @click="googleActionConfirmation = 'none'">
                            {{ $t('None') }}
                        </a>
                        <a :class="['btn', {'btn-default': googleActionConfirmation !== 'simple', 'btn-green': googleActionConfirmation === 'simple'}]"
                            @click="googleActionConfirmation = 'simple'">
                            {{ $t('Simple') }}
                        </a>
                        <a :class="['btn', {'btn-default': googleActionConfirmation !== 'pin', 'btn-green': googleActionConfirmation === 'pin'}]"
                            @click="googleActionConfirmation = 'pin'">
                            {{ $t('PIN') }}
                        </a>
                    </div>
                </dt>
            </dl>
            <dl v-if="googleEnabled && googleActionConfirmation === 'pin'">
                <dd>{{ $t('PIN') }}</dd>
                <dt class="text-center">
                    <input
                        v-if="changingGooglePin"
                        v-focus
                        type="number"
                        v-input-digits-only
                        class="form-control text-center pin-input no-spinner d-inline-block"
                        v-model="googleSettings.pin">
                    <a v-else @click="changingGooglePin = true">{{ $t('change') }}</a>
                </dt>
            </dl>
            <transition-expand>
                <div class="alert alert-danger mt-3 mb-0" v-if="googlePinError">
                    {{ $t('PIN for Google must have a length between {minLength} and {maxLength} digits.', {minLength: 4, maxLength: 8}) }}
                </div>
            </transition-expand>
        </modal>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand";

    export default {
        components: {TransitionExpand},
        props: {
            channel: Object,
        },
        data() {
            return {
                googleSettings: undefined,
                changingGooglePin: false,
                googlePinError: false,
            }
        },
        methods: {
            openGoogleSettings() {
                this.googleSettings = {...(this.channel.config.googleHome || {})};
                this.changingGooglePin = !this.googleSettings.pinSet;
            },
            updateGoogleSettings() {
                this.googlePinError = false;
                const pin = this.googleSettings.pin || '';
                if (this.googleSettings.pinSet && this.changingGooglePin && (pin.length < 4 || pin.length > 8)) {
                    this.googlePinError = true;
                } else {
                    const settings = {
                        googleHomeDisabled: this.googleSettings.googleHomeDisabled,
                        needsUserConfirmation: this.googleSettings.needsUserConfirmation,
                        pinSet: this.googleSettings.pinSet,
                    };
                    if (this.googleSettings.pinSet && this.changingGooglePin) {
                        settings.pin = this.googleSettings.pin;
                    }
                    if (!this.googleSettings.pinSet && !this.googleSettings.needsUserConfirmation) {
                        settings.pin = null;
                    }
                    this.channel.config.googleHome = settings;
                    this.$emit('change');
                    this.googleSettings = undefined;
                }
            }
        },
        computed: {
            googleEnabled: {
                get() {
                    return !this.googleSettings.googleHomeDisabled;
                },
                set(value) {
                    this.googleSettings.googleHomeDisabled = !value;
                }
            },
            googleActionConfirmation: {
                get() {
                    if (this.googleSettings.needsUserConfirmation) {
                        return 'simple';
                    } else if (this.googleSettings.pinSet) {
                        return 'pin';
                    } else {
                        return 'none';
                    }
                },
                set(value) {
                    if (value === 'simple') {
                        this.googleSettings.needsUserConfirmation = true;
                        this.googleSettings.pinSet = false;
                    } else if (value === 'pin') {
                        this.googleSettings.needsUserConfirmation = false;
                        this.googleSettings.pinSet = true;
                    } else {
                        this.googleSettings.needsUserConfirmation = false;
                        this.googleSettings.pinSet = false;
                    }
                }
            }
        }
    };
</script>

<style lang="scss" scoped>
    .pin-input {
        max-width: 130px;
    }
</style>

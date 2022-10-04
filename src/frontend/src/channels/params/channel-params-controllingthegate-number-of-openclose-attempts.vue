<template>
    <div>
        <h4 class="text-center">{{ $t("Gate's state verification") }}</h4>
        <dl>
            <dd v-tooltip="$t('channelConfigHelp_numberOfAttemptsGate')">
                {{ $t('Number of attempts to open') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <div class="btn-group btn-group-flex">
                    <a :class="['btn', (numberOfAttemptsToOpen == number ? 'btn-green' : 'btn-default'), {'btn-attempt-1': number === 1}]"
                        v-for="number in possibleNumbers"
                        :key="number"
                        @click="numberOfAttemptsToOpen = number">
                        {{ number }}
                    </a>
                </div>
            </dt>
            <dd v-tooltip="$t('channelConfigHelp_numberOfAttemptsGate')">
                {{ $t('Number of attempts to close') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <div class="btn-group btn-group-flex">
                    <a :class="['btn', (numberOfAttemptsToClose == number ? 'btn-green' : 'btn-default'), {'btn-attempt-1': number === 1}]"
                        v-for="number in possibleNumbers"
                        :key="number"
                        @click="numberOfAttemptsToClose = number">
                        {{ number }}
                    </a>
                </div>
            </dt>
        </dl>
        <transition-expand>
            <div class="alert alert-warning mt-3 small"
                v-if="numberOfAttemptsToOpen === 1 || numberOfAttemptsToClose === 1">
                {{ $t('Setting the number of attempts to 1 disables the retrying behavior completely.') }}
            </div>
        </transition-expand>
        <dl>
            <dd v-tooltip="$t('channelConfigHelp_stateVerificationMethod')">
                {{ $t('Verification method') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <div class="btn-group btn-group-flex">
                    <a :class="['btn', (stateVerificationMethodActive ? 'btn-green' : 'btn-default')]"
                        @click="stateVerificationMethodActive = true">
                        {{ $t('channelConfig_stateVerificationMethod_active') }}
                    </a>
                    <a :class="['btn', (!stateVerificationMethodActive ? 'btn-green' : 'btn-default')]"
                        @click="stateVerificationMethodActive = false">
                        {{ $t('channelConfig_stateVerificationMethod_passive') }}
                    </a>
                </div>
            </dt>
        </dl>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";

    export default {
        components: {TransitionExpand},
        props: ['channel'],
        data() {
            return {
                possibleNumbers: [1, 2, 3, 4, 5],
            };
        },
        computed: {
            numberOfAttemptsToOpen: {
                get() {
                    return this.channel.config.numberOfAttemptsToOpen;
                },
                set(value) {
                    this.channel.config.numberOfAttemptsToOpen = value;
                    this.$emit('change');
                }
            },
            numberOfAttemptsToClose: {
                get() {
                    return this.channel.config.numberOfAttemptsToClose;
                },
                set(value) {
                    this.channel.config.numberOfAttemptsToClose = value;
                    this.$emit('change');
                }
            },
            stateVerificationMethodActive: {
                get() {
                    return this.channel.config.stateVerificationMethodActive;
                },
                set(value) {
                    this.channel.config.stateVerificationMethodActive = value;
                    this.$emit('change');
                }
            },
        },
    };
</script>

<style lang="scss">
    @import "../../styles/variables";

    .btn-attempt-1.btn-green {
        background: $supla-orange;
        border: 1px solid $supla-orange;
        outline-color: $supla-orange !important;
        color: $supla-white;
        &:hover {
            background: $supla-orange;
            color: $supla-white;
        }
    }
</style>

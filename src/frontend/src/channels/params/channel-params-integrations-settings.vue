<template>
    <div class="channel-params-integrations-settings">
        <h4 class="text-center">{{ $t("Integrations settings") }}</h4>
        <div class="btn-group d-flex align-items-center justify-content-center">
            <a class="btn btn-default"
                @click="$emit('input', time)">
                Alexa<sup>&reg;</sup>
            </a>
            <a class="btn btn-default"
                @click="google = true">
                Google<sup>&reg;</sup>
            </a>
        </div>
        <modal v-if="google" class="modal-450"
            :header="$t('{serviceName} integration', {serviceName: 'Google®'})"
            @confirm="(changing = false) || $emit('change')">
            <dl>
                <dd>{{ $t('Available') }}</dd>
                <dt class="text-center">
                    <toggler v-model="channel.config.invertedLogic"></toggler>
                </dt>
            </dl>
            <dl>
                <dd>{{ $t('Action confirmation') }}</dd>
                <dt class="text-center">
                    <div class="btn-group d-flex align-items-center justify-content-center">
                        <a class="btn btn-default"
                            @click="$emit('input', time)">
                            None
                        </a>
                        <a class="btn btn-default"
                            @click="$emit('input', time)">
                            Simple
                        </a>
                        <a class="btn btn-green"
                            @click="google = true">
                            PIN
                        </a>
                    </div>
                </dt>
            </dl>
            <dl>
                <dd>{{ $t('PIN') }}</dd>
                <dt class="text-center d-flex align-items-center">
                    <input type="number"
                        class="form-control text-center pin-input"
                        v-model="channel.config.sectionsCount"
                        @input="$emit('change')">
                </dt>
            </dl>
        </modal>
    </div>
</template>

<script setup>
    import {ref} from "vue";

    const props = defineProps({channel: Object});
    const google = ref(false);


</script>

<style lang="scss" scoped>
    .pin-input {
        max-width: 130px;
    }
</style>

<template>
    <dl>
        <dd>{{ $t('Temperature adjustment') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    :min="minAdjustment"
                    :max="maxAdjustment"
                    class="form-control text-center"
                    v-model="channel.config.temperatureAdjustment"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    &deg;C
                </span>
            </span>
        </dt>
    </dl>
</template>

<script setup>
    import {computed} from "vue";

    const props = defineProps({channel: Object});

    const minAdjustment = computed(() => props.channel.config.minTemperatureAdjustment || -10);
    const maxAdjustment = computed(() => props.channel.config.maxTemperatureAdjustment || 10);
</script>

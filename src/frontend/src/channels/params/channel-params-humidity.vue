<template>
    <dl v-if="canDisplaySetting('humidityAdjustment')">
        <dd>{{ $t('Humidity adjustment') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    :min="minAdjustment"
                    :max="maxAdjustment"
                    class="form-control text-center"
                    v-model="channel.config.humidityAdjustment"
                    @input="$emit('change')">
                <span class="input-group-addon">
                    %
                </span>
            </span>
        </dt>
    </dl>
</template>

<script setup>
    import {computed} from "vue";
    import {useDisplaySettings} from "@/channels/params/useDisplaySettings";

    const props = defineProps({channel: Object});

    const minAdjustment = computed(() => props.channel.config.minHumidityAdjustment || -10);
    const maxAdjustment = computed(() => props.channel.config.maxHumidityAdjustment || 10);

    const {canDisplaySetting} = useDisplaySettings(props.channel);
</script>


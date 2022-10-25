<template>
    <div>
        <h4 class="text-center">{{ $t("Voltage monitoring") }}</h4>
        <dl>
            <dd>{{ $t('Enabled') }}</dd>
            <dt class="text-center">
                <toggler v-model="enabled" @input="$emit('change')"/>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="enabled">
                <dd>{{ $t('Lower voltage threshold') }}</dd>
                <dt class="text-center">
                    <span class="input-group">
                        <input type="number"
                            step="0.01"
                            min="5"
                            max="240"
                            class="form-control"
                            v-model="lowerVoltageThreshold"
                            @change="$emit('change')">
                        <span class="input-group-addon">V</span>
                    </span>
                </dt>
                <dd>{{ $t('Upper voltage threshold') }}</dd>
                <dt class="text-center">
                    <span class="input-group">
                        <input type="number"
                            step="0.01"
                            min="10"
                            max="500"
                            class="form-control"
                            v-model="upperVoltageThreshold"
                            @change="$emit('change')">
                        <span class="input-group-addon">V</span>
                    </span>
                </dt>
            </dl>
        </transition-expand>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand";

    const props = defineProps({channel: Object});

    let lastLowerThreshold = +props.channel.config.lowerVoltageThreshold || 220;
    let lastUpperThreshold = +props.channel.config.lowerVoltageThreshold || 250;

    const lowerVoltageThreshold = computed({
        get() {
            return +props.channel.config.lowerVoltageThreshold || undefined;
        },
        set(value) {
            props.channel.config.lowerVoltageThreshold = +value;
            lastLowerThreshold = +value;
            if (props.channel.config.upperVoltageThreshold && props.channel.config.upperVoltageThreshold <= value) {
                upperVoltageThreshold.value = Math.min(500, props.channel.config.lowerVoltageThreshold + 5);
            }
        }
    });

    const upperVoltageThreshold = computed({
        get() {
            return +props.channel.config.upperVoltageThreshold || undefined;
        },
        set(value) {
            props.channel.config.upperVoltageThreshold = +value;
            lastUpperThreshold = +value;
            if (props.channel.config.lowerVoltageThreshold && props.channel.config.lowerVoltageThreshold >= value) {
                lowerVoltageThreshold.value = Math.max(5, props.channel.config.upperVoltageThreshold - 5);
            }
        }
    });

    const enabled = computed({
        get() {
            return lowerVoltageThreshold.value > 0 || upperVoltageThreshold.value > 0;
        },
        set(enabled) {
            props.channel.config.lowerVoltageThreshold = enabled ? lastLowerThreshold : 0;
            props.channel.config.upperVoltageThreshold = enabled ? lastUpperThreshold : 0;
        }
    });
</script>


<template>
    <div>
        <dl>
            <dd>
                {{ $t('Enabled') }}
            </dd>
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
                            @blur="adjustUpperThresholdIfNeeded()"
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
                            @blur="adjustLowerThresholdIfNeeded()"
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
        }
    });

    const upperVoltageThreshold = computed({
        get() {
            return +props.channel.config.upperVoltageThreshold || undefined;
        },
        set(value) {
            props.channel.config.upperVoltageThreshold = +value;
            lastUpperThreshold = +value;
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

    function adjustUpperThresholdIfNeeded() {
        if (lowerVoltageThreshold.value >= upperVoltageThreshold.value) {
            upperVoltageThreshold.value = Math.min(500, lowerVoltageThreshold.value + 5);
        }
    }

    function adjustLowerThresholdIfNeeded() {
        if (lowerVoltageThreshold.value >= upperVoltageThreshold.value) {
            lowerVoltageThreshold.value = Math.max(5, upperVoltageThreshold.value - 5);
        }
    }
</script>


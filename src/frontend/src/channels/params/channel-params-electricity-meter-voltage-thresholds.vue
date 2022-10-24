<template>
    <div>
        <dl>
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
    </div>
</template>

<script setup>
    import {computed} from "vue";

    const props = defineProps({channel: Object});

    const lowerVoltageThreshold = computed({
        get() {
            return +props.channel.config.lowerVoltageThreshold;
        },
        set(value) {
            props.channel.config.lowerVoltageThreshold = +value;
            if (props.channel.config.upperVoltageThreshold && props.channel.config.upperVoltageThreshold <= value) {
                props.channel.config.upperVoltageThreshold = Math.min(500, props.channel.config.lowerVoltageThreshold + 5);
            }
        }
    });

    const upperVoltageThreshold = computed({
        get() {
            return +props.channel.config.upperVoltageThreshold;
        },
        set(value) {
            props.channel.config.upperVoltageThreshold = +value;
            if (props.channel.config.lowerVoltageThreshold && props.channel.config.lowerVoltageThreshold >= value) {
                props.channel.config.lowerVoltageThreshold = Math.max(5, props.channel.config.upperVoltageThreshold - 5);
            }
        }
    });
</script>


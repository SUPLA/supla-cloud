<template>
    <dl>
        <dd>{{ $t('Humidity adjustment') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    min="-10"
                    max="10"
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

<script>
    export default {
        props: ['channel'],
    };
</script>

<template>
    <div>
        <dl v-if="channel.config.timeSettingAvailable">
            <dd>{{ $t('Relay switching time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.5"
                        min="0.5"
                        max="7200"
                        class="form-control text-center"
                        v-model="channel.config.relayTimeS">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['channel'],
    };
</script>

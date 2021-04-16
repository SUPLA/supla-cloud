<template>
    <div>
        <dl v-if="timeSelectorEnabled">
            <dd>{{ $t('Relay switching time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.5"
                        min="0.5"
                        max="7200"
                        class="form-control text-center"
                        v-model="param1">
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
        computed: {
            param1: {
                set(value) {
                    this.channel.param1 = Math.round(value * 10);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param1 / 10;
                }
            },
            timeSelectorEnabled() {
                return this.channel && !(this.channel.flags & 0x00100000);
            },
        }
    };
</script>

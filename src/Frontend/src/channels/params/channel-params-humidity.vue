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
                    v-model="param3">
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
        computed: {
            param3: {
                set(value) {
                    this.channel.param3 = Math.round(value * 100);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param3 / 100;
                }
            }
        }
    };
</script>

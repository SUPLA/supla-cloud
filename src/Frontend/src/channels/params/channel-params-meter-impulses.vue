<template>
    <dl>
        <dd>{{ $t('Impulses') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="1"
                    min="0"
                    max="1000000"
                    class="form-control text-center"
                    v-model="param3">
                <span class="input-group-addon">
                    /{{ _unit }}
                </span>
            </span>
        </dt>
    </dl>
</template>

<script>
    export default {
        props: ['channel', 'unit'],
        computed: {
            param3: {
                set(value) {
                    this.channel.param3 = value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param3;
                }
            },
            _unit: {
                get() {
                    return this.unit ? this.unit : 'm³';
                }
            },
        },
    };
</script>

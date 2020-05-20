<template>
    <dl>
        <dd>{{ $t('Initial value') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.01"
                    min="0"
                    max="1000000"
                    class="form-control text-center"
                    v-model="initialvalue">
                <span class="input-group-addon">
                    {{ unit }}
                </span>
            </span>
        </dt>
    </dl>
</template>

<script>
    export default {
        props: ['channel', 'unit'],
        computed: {
            initialvalue: {
                set(value) {
                    this.channel.param1 = Math.round(value * 100);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param1 / 100;
                }
            }
        },
    };
</script>

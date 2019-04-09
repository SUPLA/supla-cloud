<template>
    <dl>
        <dd>{{ $t('Initial value') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="1"
                    min="-1000000"
                    max="1000000"
                    class="form-control text-center"
                    v-model="initial-value">
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
            impulses: {
                set(value) {
                    this.channel.param1 = value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param1;
                }
            }
        },
    };
</script>

<template>
    <dl>
        <dd>{{ $t('Temperature adjustment') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    min="-10"
                    max="10"
                    class="form-control text-center"
                    v-model="param2">
                <span class="input-group-addon">
                    &deg;C
                </span>
            </span>
        </dt>
    </dl>
</template>

<script>
    export default {
        props: ['channel'],
        computed: {
            param2: {
                set(value) {
                    this.channel.param2 = Math.round(value * 100);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param2 / 100;
                }
            }
        }
    };
</script>

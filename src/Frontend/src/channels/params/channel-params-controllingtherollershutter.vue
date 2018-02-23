<template>
    <dl>
        <dd>{{ $t('Full opening time') }}</dd>
        <dt>
            <input type="number"
                step="0.1"
                min="0"
                max="300"
                class="form-control"
                v-model="param1">
        </dt>
        <dd>{{ $t('Full closing time') }}</dd>
        <dt>
            <input type="number"
                step="0.1"
                min="0"
                max="300"
                class="form-control"
                v-model="param3">
        </dt>
    </dl>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";

    export default {
        components: {ChannelParamsControllingAnyLock},
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
            param3: {
                set(value) {
                    this.channel.param3 = Math.round(value * 10);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param3 / 10;
                }
            }
        }
    };
</script>

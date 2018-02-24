<template>
    <div>
        <dl>
            <dd>{{$t('Inverted logic')}}</dd>
            <dt class="text-center">
                <toggler v-model="invertedSensorLogic"></toggler>
            </dt>
        </dl>
    </div>
</template>

<script>
    import Toggler from "../../common/gui/toggler";

    export default {
        components: {Toggler},
        props: ['channel'],
        computed: {
            invertedSensorLogic: {
                set(inverted) {
                    this.channel.param3 = inverted ? 1 : 0;
                    this.$emit('change');
                },
                get() {
                    return !!this.channel.param3;
                }
            }
        }
    };
</script>

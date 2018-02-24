<template>
    <div>
        <dl>
            <dd>{{$t('Inverted logic')}}</dd>
            <dt class="text-center">
                <switches v-model="invertedSensorLogic"
                    type-bold="true"
                    :color="invertedSensorLogic ? 'green' : 'default'"
                    :emit-on-mount="false"></switches>
            </dt>
        </dl>
    </div>
</template>

<script>
    import Switches from "vue-switches";

    export default {
        components: {Switches},
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

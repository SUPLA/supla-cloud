<template>
    <switches v-model="model"
        :type-bold="bold"
        :label="$t(label)"
        :color="model ? 'green' : 'default'"
        :emit-on-mount="false"></switches>
</template>

<script>
    import Switches from "vue-switches";

    export default {
        props: ['value', 'invert', 'label', 'bold'],
        components: {Switches},
        computed: {
            model: {
                set(value) {
                    this.$emit('input', this.invert ? !value : value);
                },
                get() {
                    return this.invert ? !this.value : !!this.value;
                }
            }
        }
    };
</script>

<template>
<!--    <switches v-model="model"-->
<!--        :type-bold="bold"-->
<!--        :label="$t(label || '')"-->
<!--        :color="model ? trueColorValue : 'default'"-->
<!--        :emit-on-mount="false"-->
<!--        :disabled="disabled"></switches>-->
  <label>
    <input type="checkbox" :disabled="disabled" v-model="model">
    {{ $t(label || '')}}
  </label>
</template>

<script>
    // import Switches from "vue-switches";

    export default {
        props: ['value', 'invert', 'label', 'bold', 'trueColor', 'disabled'],
        // components: {Switches},
        computed: {
            trueColorValue() {
                return this.trueColor || 'green';
            },
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

<style lang="scss">
    @use "../../styles/variables" as *;
    @use 'sass:color';

    .vue-switcher-color--white {
        div {
            background-color: $supla-white;
            &:after {
                background-color: color.adjust($supla-white, $lightness: -10%);
            }
        }
    }
</style>

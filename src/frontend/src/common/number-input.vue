<template>
    <VueNumber v-model="theValue"
        :max="max"
        v-bind="{decimal: '.', separator: ' ', precision,  suffix}"
        class="form-control text-center mt-2"
        @change="onChange()"
        @blur="onBlur()"/>
</template>

<script>
    import {component as VueNumber} from '@coders-tm/vue-number-format'

    export default {
        components: {VueNumber},
        props: {
            value: {
                type: Number,
                default: 0,
            },
            min: Number,
            max: Number,
            suffix: String,
            precision: {
                type: Number,
                default: 0,
            },
        },
        data() {
            return {
                theValue: 0,
            };
        },
        beforeMount() {
            this.theValue = this.value || 0;
        },
        methods: {
            onChange() {
                if (this.min === undefined || this.theValue >= this.min) {
                    this.$emit('input', +this.theValue);
                }
            },
            onBlur() {
                if (this.theValue === "" && this.min !== undefined) {
                    this.theValue = 0;
                }
                if (this.min !== undefined && this.theValue < this.min) {
                    this.theValue = this.min;
                }
                this.onChange();
            },
        },
        watch: {
            value(newValue) {
                if (newValue !== this.theValue) {
                    this.theValue = newValue;
                }
            }
        }
    };
</script>

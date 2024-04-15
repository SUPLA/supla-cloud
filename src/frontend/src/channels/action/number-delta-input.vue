<template>
    <input type="text"
        class="form-control"
        v-model="theValue"
        @keydown="validateInput"
        @blur="onChange()">
</template>

<script>
    export default {
        props: {
            value: [String, Number],
            min: Number,
            max: Number,
        },
        data() {
            return {
                theValue: '',
            };
        },
        mounted() {
            if (this.value !== undefined) {
                this.theValue = '' + this.value;
            }
        },
        methods: {
            adjustTheValue() {
                this.theValue = this.theValue.match(/^[+-]?[0-9]+/)?.[0] || '';
                if (this.theValue !== '') {
                    const sign = this.theValue[0] === '+' ? '+' : '';
                    let value = +this.theValue;
                    if (this.min && value < this.min) {
                        value = this.min;
                    }
                    if (this.max && value > this.max) {
                        value = this.max;
                    }
                    this.theValue = sign + value;
                }
            },
            onChange() {
                this.adjustTheValue();
                this.$emit('input', this.theValue);
            },
            validateInput(e) {
                if (e.keyCode > 47 && !+e.key && !['0', '+', '-'].includes(e.key)) {
                    e.preventDefault();
                }
            },
            ensureBetween(value, min, max) {
                if (value < min) {
                    return min;
                } else if (value > max) {
                    return max;
                } else {
                    return +value;
                }
            }
        },
        // watch: {
        //     value() {
        //         if (this.value && this.value.percentage === undefined) {
        //             Vue.nextTick(() => this.onChange());
        //         } else {
        //             this.percentage = this.value?.percentage || 0;
        //         }
        //     },
        // },
    };
</script>

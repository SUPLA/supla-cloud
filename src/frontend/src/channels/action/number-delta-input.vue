<template>
    <input type="text"
        class="form-control"
        v-model="theValue"
        @keydown="validateInput"
        :maxlength="max ? ('' + max).length + 1 : 10"
        @input="onInput()"
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
                const value = this.theValue.match(/^[+-]?[0-9]+/)?.[0] || '';
                if (value !== '') {
                    const sign = this.theValue[0] === '+' ? '+' : '';
                    let value = +this.theValue;
                    if (this.min && value < this.min) {
                        value = this.min;
                    }
                    if (this.max && value > this.max) {
                        value = this.max;
                    }
                    return sign + value;
                } else {
                    return '';
                }
            },
            onInput() {
                const newValue = this.adjustTheValue();
                if (this.theValue === newValue) {
                    this.onChange();
                }
            },
            onChange() {
                this.theValue = this.adjustTheValue();
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
        watch: {
            value() {
                if (this.value !== this.theValue) {
                    this.theValue = '' + this.value;
                }
            },
        },
    };
</script>

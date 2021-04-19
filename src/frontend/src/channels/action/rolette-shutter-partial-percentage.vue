<template>
    <div>
        <span class="input-group">
            <input type="number"
                min="0"
                max="100"
                step="5"
                class="form-control"
                maxlength="3"
                v-model="percentage"
                @change="onChange()">
            <span class="input-group-addon">%</span>
        </span>
    </div>
</template>

<script>
    import Vue from "vue";

    export default {
        props: ['value'],
        data() {
            return {
                percentage: 0
            };
        },
        mounted() {
            if (this.value) {
                this.percentage = this.value.percentage || 0;
            }
            Vue.nextTick(() => this.onChange());
        },
        methods: {
            onChange() {
                this.percentage = this.ensureBetween(this.percentage, 0, 100);
                this.$emit('input', {percentage: this.percentage});
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
        }
    };
</script>

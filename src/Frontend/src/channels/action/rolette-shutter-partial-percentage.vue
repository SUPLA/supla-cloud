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
        name: 'rolette-shutter-partial-percentage',
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
            if (this.value.percentage === undefined) {
                Vue.nextTick(() => this.onChange());
            }
        },
        methods: {
            onChange() {
                this.$emit('input', {percentage: this.percentage});
            }
        }
    };
</script>

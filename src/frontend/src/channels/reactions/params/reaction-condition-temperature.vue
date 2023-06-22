<template>
    <div class="form-group">
        <label>{{ $t('Threshold') }}</label>
        <span class="input-group">
            <span class="input-group-btn">
                <a class="btn btn-white" @click="operator = operator === 'lt' ? 'gt' : 'lt'">
                    <span v-if="operator === 'lt'">{{ $t('decreases below') }}</span>
                    <span v-else>{{ $t('raises above') }}</span>
                </a>
            </span>
            <input type="number"
                required
                v-focus="true"
                v-model="threshold"
                class="form-control">
            <span class="input-group-addon">
                &deg;C
            </span>
        </span>
    </div>
</template>

<script>
    export default {
        props: {
            value: Object,
        },
        mounted() {
            if (!this.value) {
                this.updateModel();
            }
        },
        methods: {
            updateModel() {
                this.onChangeTo = {[this.operator]: this.threshold};
            }
        },
        computed: {
            operator: {
                get() {
                    return (this.onChangeTo && Object.hasOwn(this.onChangeTo, 'gt')) ? 'gt' : 'lt';
                },
                set(operator) {
                    this.onChangeTo = {[operator]: this.threshold};
                },
            },
            threshold: {
                get() {
                    const threshold = this.onChangeTo?.lt || this.onChangeTo?.gt;
                    return Number.isFinite(threshold) ? threshold : 20;
                },
                set(threshold) {
                    this.onChangeTo = {[this.operator]: +threshold};
                },
            },
            onChangeTo: {
                get() {
                    return this.value?.on_change_to;
                },
                set(value) {
                    this.$emit('input', {on_change_to: {...value, name: 'temperature'}});
                }
            }
        }
    }
</script>

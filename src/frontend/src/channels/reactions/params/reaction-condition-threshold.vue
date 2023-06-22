<template>
    <div class="form-group">
        <label>{{ $t(label) }}</label>
        <span class="input-group">
            <span class="input-group-btn">
                <a class="btn btn-white" @click="nextOperator()">
                    <span v-if="operator === 'lt'">&lt;</span>
                    <span v-else-if="operator === 'le'">&le;</span>
                    <span v-else-if="operator === 'gt'">&gt;</span>
                    <span v-else>&ge;</span>
                </a>
            </span>
            <input type="number"
                required
                v-focus="true"
                v-model="threshold"
                class="form-control">
            <span class="input-group-addon">{{ unit }}</span>
        </span>
    </div>
</template>

<script>
    const OPERATORS = ['lt', 'le', 'gt', 'ge'];
    export default {
        props: {
            value: Object,
            unit: {
                type: String,
                default: '',
            },
            field: {
                type: String,
                required: true,
            },
            label: {
                type: String,
                default: 'Threshold', // i18n
            }
        },
        mounted() {
            if (!this.value) {
                this.updateModel();
            }
        },
        methods: {
            updateModel() {
                this.onChangeTo = {[this.operator]: this.threshold};
            },
            nextOperator() {
                const nextIndex = OPERATORS.indexOf(this.operator) + 1;
                this.operator = nextIndex >= OPERATORS.length ? OPERATORS[0] : OPERATORS[nextIndex];
            },
        },
        computed: {
            operator: {
                get() {
                    const operator = this.onChangeTo ? OPERATORS.find(op => Object.hasOwn(this.onChangeTo, op)) : undefined;
                    return operator || OPERATORS[0];
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
                    this.$emit('input', {on_change_to: {...value, name: this.field}});
                }
            }
        }
    }
</script>

<template>
    <div class="reaction-condition-threshold">
        <div class="form-group d-flex align-items-center">
            <label class="flex-grow-1 pr-3">{{ $t(labelI18n(field)) }}</label>
            <span class="input-group">
                <span class="input-group-btn">
                    <a class="btn btn-white" @click="nextOperator()">
                        <span v-if="operator === 'eq'">=</span>
                        <span v-else-if="operator === 'ne'">&ne;</span>
                        <span v-else-if="operator === 'lt'">&lt;</span>
                        <span v-else-if="operator === 'le'">&le;</span>
                        <span v-else-if="operator === 'gt'">&gt;</span>
                        <span v-else>&ge;</span>
                    </a>
                </span>
                <span class="input-group-addon" v-if="unitBefore(field, subject)">{{ $t(unitBefore(field, subject)) }}</span>
                <input type="number"
                    required
                    v-model="threshold"
                    :step="step()" :min="min()" :max="max()"
                    @input="updateModel(true)"
                    class="form-control">
                <span class="input-group-addon" v-if="unit(field, subject)">{{ $t(unit(field, subject)) }}</span>
            </span>
        </div>
        <div class="form-group d-flex align-items-center" v-if="resumeOperator">
            <span class="flex-grow-1 pr-4">
                {{ $t('then execute the action') }}
                {{ $t(resumeLabelI18n(field)) }}
            </span>
            <span class="input-group">
                <span class="input-group-addon">
                    <span v-if="resumeOperator === 'lt'">&lt;</span>
                    <span v-else-if="resumeOperator === 'le'">&le;</span>
                    <span v-else-if="resumeOperator === 'gt'">&gt;</span>
                    <span v-else>&ge;</span>
                </span>
                <span class="input-group-addon" v-if="unitBefore(field, subject)">{{ $t(unitBefore(field, subject)) }}</span>
                <input type="number" required class="form-control" v-model="resumeThreshold"
                    :step="step()"
                    @input="updateModel()"
                    :min="['lt', 'le'].includes(operator) ? threshold : min()"
                    :max="['gt', 'ge'].includes(operator) ? threshold : max()">
                <span class="input-group-addon" v-if="unit(field, subject)">{{ $t(unit(field, subject)) }}</span>
            </span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            value: Object,
            subject: Object,
            field: String,
            operators: {
                type: Array,
                default: () => ['lt', 'le', 'gt', 'ge', 'eq', 'ne'],
            },
            min: {
                type: Function,
                default: () => undefined,
            },
            max: {
                type: Function,
                default: () => undefined,
            },
            step: {
                type: Function,
                default: () => 0.01,
            },
            unit: {
                type: Function,
                default: () => '',
            },
            unitBefore: {
                type: Function,
                default: () => '',
            },
            labelI18n: {
                type: Function,
                default: () => 'When the value will be', // i18n
            },
            resumeLabelI18n: {
                type: Function,
                default: () => 'and wait until the value will be', // i18n
            },
            defaultThreshold: {
                type: Number,
                default: 20,
            },
            disableResume: Boolean,
        },
        data() {
            return {
                operator: 'lt',
                threshold: this.defaultThreshold,
                resumeThreshold: this.defaultThreshold,
            };
        },
        mounted() {
            this.updateInternalState();
            if (!this.value) {
                this.updateModel();
            }
            if (!this.resumeThreshold) {
                this.resumeThreshold = this.threshold;
            }
        },
        methods: {
            updateInternalState() {
                this.operator = this.operators.find(op => Object.hasOwn(this.onChangeTo, op)) || this.operators[0];
                this.threshold = Number.isFinite(this.onChangeTo[this.operator]) ? this.onChangeTo[this.operator] : this.defaultThreshold;
                const resume = this.onChangeTo.resume || {};
                this.resumeThreshold = Number.isFinite(resume[this.resumeOperator]) ? resume[this.resumeOperator] : this.defaultThreshold;
            },
            updateModel(adjustResumeThreshold = false) {
                if (adjustResumeThreshold && this.resumeOperator) {
                    this.adjustResumeThreshold();
                }
                if (this.validValues) {
                    const value = {[this.operator]: parseFloat(this.threshold)};
                    if (this.resumeOperator) {
                        value.resume = {[this.resumeOperator]: parseFloat(this.resumeThreshold)};
                    }
                    this.onChangeTo = value;
                } else {
                    this.onChangeTo = undefined;
                }
            },
            nextOperator() {
                const nextIndex = this.operators.indexOf(this.operator) + 1;
                this.operator = nextIndex >= this.operators.length ? this.operators[0] : this.operators[nextIndex];
                this.updateModel();
            },
            adjustResumeThreshold() {
                if (['lt', 'le'].includes(this.operator)) {
                    this.resumeThreshold = Math.max(this.resumeThreshold, this.threshold);
                } else {
                    this.resumeThreshold = Math.min(this.resumeThreshold, this.threshold);
                }
            }
        },
        computed: {
            resumeOperator() {
                return !this.disableResume && {lt: 'ge', le: 'gt', ge: 'lt', gt: 'le'}[this.operator];
            },
            onChangeTo: {
                get() {
                    return this.value?.on_change_to || {};
                },
                set(value) {
                    this.$emit('input', value ? {on_change_to: {...value, name: this.field}} : undefined);
                }
            },
            resumeMin() {
                return ['lt', 'le'].includes(this.operator) ? this.threshold : this.min();
            },
            resumeMax() {
                return ['gt', 'ge'].includes(this.operator) ? this.threshold : this.max()
            },
            validValues() {
                return Number.isFinite(parseFloat(this.threshold)) &&
                    (!this.resumeOperator || Number.isFinite(parseFloat(this.resumeThreshold))) &&
                    (!Number.isFinite(this.min()) || this.threshold >= this.min()) &&
                    (!Number.isFinite(this.max()) || this.threshold <= this.max()) &&
                    (!Number.isFinite(this.resumeMin) || this.resumeThreshold >= this.resumeMin) &&
                    (!Number.isFinite(this.resumeMax) || this.resumeThreshold <= this.resumeMax);
            },
        },
        watch: {
            field() {
                this.updateModel();
            },
            defaultThreshold(currentValue, previousValue) {
                if (this.threshold == previousValue && (!this.resumeOperator || this.resumeThreshold == previousValue)) {
                    this.threshold = currentValue;
                    this.resumeThreshold = currentValue;
                    this.updateModel();
                }
            },
            operators(currentValue, previousValue) {
                if (currentValue.length !== previousValue.length) {
                    this.operator = this.operators[0];
                }
            },
        }
    }
</script>

<style lang="scss">
    .reaction-condition-threshold {
        input[type=number] {
            width: 95px;
        }
    }
</style>

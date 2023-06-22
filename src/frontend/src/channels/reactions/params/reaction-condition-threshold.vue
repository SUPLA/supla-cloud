<template>
    <div>
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
        <p class="text-right">
            {{ $t('then execute the action') }}
            {{ $t(suspendLabel) }}
        </p>
        <div class="row">
            <div class="col-xs-8 col-xs-offset-4">

                <div class="form-group">
                    <span class="input-group">
                        <span class="input-group-addon">
                            <span v-if="resumeOperator === 'lt'">&lt;</span>
                            <span v-else-if="resumeOperator === 'le'">&le;</span>
                            <span v-else-if="resumeOperator === 'gt'">&gt;</span>
                            <span v-else>&ge;</span>
                        </span>
                        <input type="number" required class="form-control" v-model="resumeThreshold"
                            :min="['lt', 'le'].includes(operator) ? threshold : undefined"
                            :max="['gt', 'ge'].includes(operator) ? threshold : undefined">
                        <span class="input-group-addon">{{ unit }}</span>
                    </span>
                </div>
            </div>
        </div>
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
            },
            suspendLabel: {
                type: String,
                default: 'Suspend until', // i18n
            }
        },
        data() {
            return {
                operatorValue: undefined,
                thresholdValue: undefined,
                resumeThresholdValue: undefined,
            };
        },
        mounted() {
            this.operatorValue = this.operator;
            this.thresholdValue = this.threshold;
            this.resumeThresholdValue = this.resumeThreshold;
            if (!this.value) {
                this.updateModel();
            }
        },
        methods: {
            updateModel() {
                const value = {[this.operatorValue]: this.thresholdValue, resume: {[this.resumeOperator]: this.resumeThresholdValue}};
                this.onChangeTo = value;
            },
            nextOperator() {
                const nextIndex = OPERATORS.indexOf(this.operator) + 1;
                this.operator = nextIndex >= OPERATORS.length ? OPERATORS[0] : OPERATORS[nextIndex];
            },
            adjustResumeThreshold() {
                if (['lt', 'le'].includes(this.operatorValue)) {
                    this.resumeThresholdValue = Math.max(this.resumeThresholdValue, this.thresholdValue);
                } else {
                    this.resumeThresholdValue = Math.min(this.resumeThresholdValue, this.thresholdValue);
                }
            }
        },
        computed: {
            operator: {
                get() {
                    const operator = this.onChangeTo ? OPERATORS.find(op => Object.hasOwn(this.onChangeTo, op)) : undefined;
                    return operator || OPERATORS[0];
                },
                set(operator) {
                    this.operatorValue = operator;
                    this.adjustResumeThreshold();
                    this.updateModel();
                },
            },
            threshold: {
                get() {
                    const threshold = this.onChangeTo && this.onChangeTo[this.operator];
                    return Number.isFinite(threshold) ? threshold : 20;
                },
                set(threshold) {
                    this.thresholdValue = +threshold;
                    this.adjustResumeThreshold();
                    this.updateModel();
                },
            },
            resumeOperator() {
                return {lt: 'ge', le: 'gt', ge: 'lt', gt: 'le'}[this.operatorValue];
            },
            resumeThreshold: {
                get() {
                    const threshold = this.onChangeTo?.resume && this.onChangeTo?.resume[this.resumeOperator];
                    return Number.isFinite(threshold) ? threshold : (this.resumeThresholdValue || this.threshold);
                },
                set(threshold) {
                    this.resumeThresholdValue = +threshold;
                    this.updateModel();
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

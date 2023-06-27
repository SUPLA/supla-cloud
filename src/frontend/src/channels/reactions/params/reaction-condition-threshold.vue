<template>
    <div>
        <div class="form-group">
            <label>{{ $t(label) }}</label>
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
                <input type="number"
                    required
                    v-focus="true"
                    v-model="threshold"
                    @input="updateModel()"
                    class="form-control">
                <span class="input-group-addon">{{ unit }}</span>
            </span>
        </div>
        <p>
            {{ $t('then execute the action') }}
            <span v-if="resumeOperator">{{ $t(suspendLabel) }}</span>
        </p>
        <div class="form-group" v-if="resumeOperator">
            <span class="input-group">
                <span class="input-group-addon">
                    <span v-if="resumeOperator === 'lt'">&lt;</span>
                    <span v-else-if="resumeOperator === 'le'">&le;</span>
                    <span v-else-if="resumeOperator === 'gt'">&gt;</span>
                    <span v-else>&ge;</span>
                </span>
                <input type="number" required class="form-control" v-model="resumeThreshold"
                    @input="updateModel()"
                    :min="['lt', 'le'].includes(operator) ? threshold : undefined"
                    :max="['gt', 'ge'].includes(operator) ? threshold : undefined">
                <span class="input-group-addon">{{ unit }}</span>
            </span>
        </div>
    </div>
</template>

<script>
    const OPERATORS = ['lt', 'le', 'gt', 'ge', 'eq', 'ne'];
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
                operator: 'lt',
                threshold: 20,
                resumeThreshold: 20,
            };
        },
        mounted() {
            this.updateInternalState();
            if (!this.value) {
                this.updateModel();
            }
        },
        methods: {
            updateInternalState() {
                this.operator = OPERATORS.find(op => Object.hasOwn(this.onChangeTo, op)) || OPERATORS[0];
                this.threshold = Number.isFinite(this.onChangeTo[this.operator]) ? this.onChangeTo[this.operator] : 20;
                const resume = this.onChangeTo.resume || {};
                this.resumeThreshold = Number.isFinite(resume[this.resumeOperator]) ? resume[this.resumeOperator] : 20;
            },
            updateModel() {
                if (Number.isFinite(parseFloat(this.threshold))) {
                    const value = {[this.operator]: parseFloat(this.threshold)};
                    if (this.resumeOperator) {
                        this.adjustResumeThreshold();
                        value.resume = {[this.resumeOperator]: parseFloat(this.resumeThreshold)};
                    }
                    this.onChangeTo = value;
                } else {
                    this.onChangeTo = undefined;
                }
            },
            nextOperator() {
                const nextIndex = OPERATORS.indexOf(this.operator) + 1;
                this.operator = nextIndex >= OPERATORS.length ? OPERATORS[0] : OPERATORS[nextIndex];
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
                return {lt: 'ge', le: 'gt', ge: 'lt', gt: 'le'}[this.operator];
            },
            onChangeTo: {
                get() {
                    return this.value?.on_change_to || {};
                },
                set(value) {
                    this.$emit('input', value ? {on_change_to: {...value, name: this.field}} : undefined);
                }
            }
        }
    }
</script>

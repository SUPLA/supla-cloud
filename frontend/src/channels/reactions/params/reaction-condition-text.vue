<template>
  <div class="reaction-condition-text">
    <div class="form-group d-flex align-items-center">
      <label class="flex-grow-1 pr-3">{{ $t(labelI18n(field)) }}</label>
      <span class="input-group">
        <span class="input-group-btn">
          <a class="btn btn-white" @click="nextOperator()">
            <span v-if="operator === 'eq'">=</span>
            <span v-else>&ne;</span>
          </a>
        </span>
        <input v-model="threshold" type="text" required class="form-control" @input="updateModel()" />
      </span>
    </div>
    <div>
      <ReactionConditionDuration v-model="duration" />
    </div>
  </div>
</template>

<script>
  import ReactionConditionDuration from '@/channels/reactions/params/reaction-condition-duration.vue';

  export default {
    compatConfig: {
      MODE: 3,
    },
    components: {ReactionConditionDuration},
    props: {
      modelValue: Object,
      field: String,
      operators: {
        type: Array,
        default: () => ['eq'],
      },
      labelI18n: {
        type: Function,
        default: () => 'When the text value will be', // i18n
      },
      defaultValue: {
        type: String,
        default: '',
      },
      defaultDurationSec: {
        type: Number,
        default: 0,
      },
    },
    data() {
      return {
        operator: this.operators[0],
        threshold: this.defaultValue,
        duration: this.defaultDurationSec,
      };
    },
    computed: {
      onChangeTo: {
        get() {
          return this.modelValue?.on_change_to || {};
        },
        set(value) {
          this.$emit('update:modelValue', value ? {on_change_to: {...value, name: this.field, duration_sec: this.duration}} : undefined);
        },
      },
      validValue() {
        return typeof this.threshold === 'string' && this.threshold.length > 0;
      },
    },
    watch: {
      field() {
        this.updateModel();
      },
      operator() {
        this.updateModel();
      },
      duration() {
        this.updateModel();
      },
      modelValue() {
        this.updateInternalState();
      },
    },
    mounted() {
      this.updateInternalState();
      if (!this.modelValue) {
        this.updateModel();
      }
    },
    methods: {
      updateInternalState() {
        this.operator = this.operators.find((op) => Object.hasOwn(this.onChangeTo, op)) || this.operators[0];
        this.threshold = typeof this.onChangeTo[this.operator] === 'string' ? this.onChangeTo[this.operator] : this.defaultValue;
        this.duration = Number.isFinite(this.onChangeTo?.duration_sec) ? this.onChangeTo.duration_sec : this.defaultDurationSec;
      },
      updateModel() {
        if (this.validValue) {
          this.onChangeTo = {[this.operator]: this.threshold};
        } else {
          this.onChangeTo = undefined;
        }
      },
      nextOperator() {
        const nextIndex = this.operators.indexOf(this.operator) + 1;
        this.operator = nextIndex >= this.operators.length ? this.operators[0] : this.operators[nextIndex];
      },
    },
  };
</script>

<style lang="scss">
  .reaction-condition-text {
    input[type='text'] {
      width: 180px;
    }
  }
</style>

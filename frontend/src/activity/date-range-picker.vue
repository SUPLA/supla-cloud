<template>
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label>{{ labelDateStart || $t('Start date') }}</label>
        <input
          v-model="dateStart"
          type="datetime-local"
          :min="minDate"
          :max="dateEnd || maxDate"
          class="form-control datetimepicker-start"
          @change="onChange()"
        />
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>{{ labelDateEnd || $t('End date') }}</label>
        <input
          v-model="dateEnd"
          type="datetime-local"
          :min="dateStart || minDate"
          :max="maxDate"
          class="form-control datetimepicker-end"
          @change="onChange()"
        />
      </div>
    </div>
  </div>
</template>

<script>
  import {DateTime} from 'luxon';
  import {formatDateForHtmlInput} from '../common/filters-date';

  export default {
    props: {
      value: Object,
      labelDateStart: {type: String},
      labelDateEnd: {type: String},
      minNow: Boolean,
      min: {type: Date, default: undefined},
      max: {type: Date, default: undefined},
    },
    data() {
      return {
        dateStart: undefined,
        dateEnd: undefined,
      };
    },
    computed: {
      minDate() {
        if (this.min !== undefined) {
          return formatDateForHtmlInput(DateTime.fromJSDate(this.min).toISO());
        } else if (this.minNow) {
          return formatDateForHtmlInput(DateTime.now().toISO());
        }
        return undefined;
      },
      maxDate() {
        if (this.max !== undefined && this.max) {
          return formatDateForHtmlInput(DateTime.fromJSDate(this.min).toISO());
        }
        return undefined;
      },
    },
    watch: {
      value() {
        this.setFromValue();
      },
    },
    mounted() {
      this.setFromValue();
    },
    methods: {
      setFromValue() {
        if (this.value) {
          if (this.value.dateStart) {
            this.dateStart = formatDateForHtmlInput(this.value.dateStart);
          } else {
            this.dateStart = undefined;
          }
          if (this.value.dateEnd) {
            this.dateEnd = formatDateForHtmlInput(this.value.dateEnd);
          } else {
            this.dateEnd = undefined;
          }
        }
      },
      onChange() {
        const format = (date) => DateTime.fromISO(date).startOf('second').toISO({suppressMilliseconds: true});
        if (this.dateStart && this.dateEnd && DateTime.fromISO(this.dateStart) >= DateTime.fromISO(this.dateEnd)) {
          this.dateEnd = formatDateForHtmlInput(DateTime.fromISO(this.dateStart).plus({minutes: 1}).toISO());
        }
        this.$emit('input', {
          dateStart: this.dateStart ? format(this.dateStart) : undefined,
          dateEnd: this.dateEnd ? format(this.dateEnd) : undefined,
        });
      },
    },
  };
</script>

<template>
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label>{{ labelDateStart || $t('Start date') }}</label>
        <input v-model="dateStart" :type="inputType" :min="minDate" :max="dateEnd || maxDate" class="form-control datetimepicker-start" @change="onChange()" />
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>{{ labelDateEnd || $t('End date') }}</label>
        <input v-model="dateEnd" :type="inputType" :min="dateStart || minDate" :max="maxDate" class="form-control datetimepicker-end" @change="onChange()" />
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
      dateOnly: Boolean,
    },
    data() {
      return {
        dateStart: undefined,
        dateEnd: undefined,
      };
    },
    computed: {
      inputType() {
        return this.dateOnly ? 'date' : 'datetime-local';
      },
      minDate() {
        if (this.min !== undefined) {
          return this.formatForInput(DateTime.fromJSDate(this.min).toISO());
        } else if (this.minNow) {
          return this.formatForInput(DateTime.now().toISO());
        }
        return undefined;
      },
      maxDate() {
        if (this.max !== undefined && this.max) {
          return this.formatForInput(DateTime.fromJSDate(this.max).toISO());
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
      formatForInput(datetime, isEnd = false) {
        const parsed = DateTime.fromISO(datetime);
        if (!this.dateOnly) {
          return formatDateForHtmlInput(datetime);
        }

        return (isEnd ? parsed.minus({days: 1}) : parsed).toISODate();
      },
      setFromValue() {
        if (this.value) {
          if (this.value.dateStart) {
            this.dateStart = this.formatForInput(this.value.dateStart);
          } else {
            this.dateStart = undefined;
          }
          if (this.value.dateEnd) {
            this.dateEnd = this.formatForInput(this.value.dateEnd, true);
          } else {
            this.dateEnd = undefined;
          }
        }
      },
      onChange() {
        const format = (date, isEnd = false) => {
          const parsed = DateTime.fromISO(date);
          if (this.dateOnly) {
            return (isEnd ? parsed.plus({days: 1}) : parsed).startOf('day').toISO({suppressMilliseconds: true});
          }
          return parsed.startOf('second').toISO({suppressMilliseconds: true});
        };
        if (this.dateStart && this.dateEnd && DateTime.fromISO(this.dateStart) > DateTime.fromISO(this.dateEnd)) {
          this.dateEnd = this.dateOnly
            ? DateTime.fromISO(this.dateStart).toISODate()
            : formatDateForHtmlInput(DateTime.fromISO(this.dateStart).plus({minutes: 1}).toISO());
        }
        this.$emit('input', {
          dateStart: this.dateStart ? format(this.dateStart) : undefined,
          dateEnd: this.dateEnd ? format(this.dateEnd, true) : undefined,
        });
      },
    },
  };
</script>

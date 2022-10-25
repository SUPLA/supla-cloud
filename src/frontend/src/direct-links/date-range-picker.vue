<template>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ $t('Start date') }}</label>
                <input type="datetime-local"
                    v-model="dateStart"
                    :min="minDate"
                    :max="dateEnd"
                    @change="onChange()"
                    class="form-control datetimepicker-start">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ $t('End date') }}</label>
                <input type="datetime-local"
                    v-model="dateEnd"
                    :min="dateStart"
                    @change="onChange()"
                    class="form-control datetimepicker-end">
            </div>
        </div>
    </div>
</template>

<script>
    import {DateTime} from "luxon";
    import {formatDateForHtmlInput} from "../common/filters-date";

    export default {
        props: ['value'],
        data() {
            return {
                dateStart: undefined,
                dateEnd: undefined,
            };
        },
        mounted() {
            if (this.value && this.value.dateStart) {
                this.dateStart = formatDateForHtmlInput(this.value.dateStart);
            }
            if (this.value && this.value.dateEnd) {
                this.dateEnd = formatDateForHtmlInput(this.value.dateEnd);
            }
        },
        methods: {
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
        computed: {
            minDate() {
                return formatDateForHtmlInput(DateTime.now().toISO());
            },
        }
    };
</script>

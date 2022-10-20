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
                this.dateStart = this.formatForHtmlInput(this.value.dateStart);
            }
            if (this.value && this.value.dateEnd) {
                this.dateEnd = this.formatForHtmlInput(this.value.dateEnd);
            }
        },
        methods: {
            onChange() {
                const format = (date) => DateTime.fromISO(date).startOf('second').toISO({suppressMilliseconds: true});
                this.$emit('input', {
                    dateStart: this.dateStart ? format(this.dateStart) : undefined,
                    dateEnd: this.dateEnd ? format(this.dateEnd) : undefined,
                });
            },
            formatForHtmlInput(datetime) {
                return DateTime.fromISO(datetime)
                    .startOf('minute')
                    .toISO({includeOffset: false, suppressSeconds: true, suppressMilliseconds: true});
            },
        },
        computed: {
            minDate() {
                return this.formatForHtmlInput(DateTime.now().toISO());
            },
        }
    };
</script>

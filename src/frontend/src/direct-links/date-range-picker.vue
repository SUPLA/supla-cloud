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
    import moment from "moment";

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
                this.dateStart = moment(this.value.dateStart).format(moment.HTML5_FMT.DATETIME_LOCAL);
            }
            if (this.value && this.value.dateEnd) {
                this.dateEnd = moment(this.value.dateEnd).format(moment.HTML5_FMT.DATETIME_LOCAL);
            }
        },
        methods: {
            onChange() {
                this.$emit('input', {
                    dateStart: this.dateStart ? moment(this.dateStart).format() : undefined,
                    dateEnd: this.dateEnd ? moment(this.dateEnd).format() : undefined,
                });
            },
        },
        computed: {
            minDate() {
                return moment().format(moment.HTML5_FMT.DATETIME_LOCAL);
            },
        }
    };
</script>

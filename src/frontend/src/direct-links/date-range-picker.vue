<template>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ $t('Start date') }}</label>
                <div class="input-group date">
                    <input type="text"
                        class="form-control datetimepicker-start"
                        ref="startDatePicker">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ $t('End date') }}</label>
                <div class="input-group date">
                    <input type="text"
                        class="form-control datetimepicker-end"
                        ref="endDatePicker">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Vue from "vue";
    import moment from "moment";
    import 'imports-loader?define=>false,exports=>false!eonasdan-bootstrap-datetimepicker';
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';

    export default {
        props: ['value'],
        data() {
            return {
                dateStart: undefined,
                dateEnd: undefined,
            };
        },
        mounted() {
            let startDatePicker = $(this.$refs.startDatePicker);
            let endDatePicker = $(this.$refs.endDatePicker);
            startDatePicker.datetimepicker({
                minDate: 'now',
                useCurrent: false,
                locale: Vue.config.lang,
                stepping: 5
            });
            endDatePicker.datetimepicker({
                minDate: moment().add(5, 'minute'),
                useCurrent: false,
                locale: Vue.config.lang,
                stepping: 5
            });
            startDatePicker.on("dp.change", (e) => {
                endDatePicker.data("DateTimePicker").minDate(e.date);
                if (e.date) {
                    this.dateStart = moment(e.date).startOf('minute');
                } else {
                    this.dateStart = undefined;
                }
                this.onChange();
            });
            endDatePicker.on("dp.change", (e) => {
                startDatePicker.data("DateTimePicker").maxDate(e.date);
                if (e.date) {
                    this.dateEnd = moment(e.date).startOf('minute');
                } else {
                    this.dateEnd = undefined;
                }
                this.onChange();
            });
            if (this.value && this.value.dateStart) {
                startDatePicker.data('DateTimePicker').date(moment(this.value.dateStart).toDate());
            }
            if (this.value && this.value.dateEnd) {
                endDatePicker.data('DateTimePicker').date(moment(this.value.dateEnd).toDate());
            }
        },
        methods: {
            onChange() {
                this.$emit('input', {dateStart: this.dateStart, dateEnd: this.dateEnd});
            }
        }
    };
</script>

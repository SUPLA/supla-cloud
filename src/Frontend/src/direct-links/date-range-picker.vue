<template>
    <div>
        <div class="form-group">
            <div class="input-group date">
                <div class="input-group-addon">{{ $t('From') }}</div>
                <input type="text"
                    class="form-control datetimepicker-start"
                    ref="startDatePicker">
            </div>
        </div>
        <div class="form-group">
            <div class="input-group date">
                <div class="input-group-addon">{{ $t('To') }}</div>
                <input type="text"
                    class="form-control datetimepicker-end"
                    ref="endDatePicker">
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
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

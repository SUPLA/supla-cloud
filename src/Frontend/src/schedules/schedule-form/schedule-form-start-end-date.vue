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

    export default {
        props: ['value'],
        data() {
            return {
                dateStart: moment(),
                dateEnd: undefined,
            };
        },
        mounted() {
            let startDatePicker = $(this.$refs.startDatePicker);
            let endDatePicker = $(this.$refs.endDatePicker);
            startDatePicker.datetimepicker({
                minDate: 'now',
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
                this.dateStart = moment(e.date ? e.date : undefined).startOf('minute').subtract(1, 'minute'); // minus to make it inclusive
                this.updateModel();
            });
            endDatePicker.on("dp.change", (e) => {
                startDatePicker.data("DateTimePicker").maxDate(e.date);
                if (e.date) {
                    this.dateEnd = moment(e.date).startOf('minute');
                } else {
                    this.dateEnd = undefined;
                }
                this.updateModel();
            });
            if (this.value) {
                if (this.value.dateStart) {
                    this.dateStart = moment(this.value.dateStart);
                    startDatePicker.data('DateTimePicker').date(moment(this.value.dateStart).toDate());
                }
                if (this.value.dateEnd) {
                    this.dateEnd = moment(this.value.dateEnd);
                    endDatePicker.data('DateTimePicker').date(moment(this.value.dateEnd).toDate());
                }
            }
        },
        methods: {
            updateModel() {
                this.$emit('input', {
                    dateStart: this.dateStart ? this.dateStart.format() : undefined,
                    dateEnd: this.dateEnd ? this.dateEnd.format() : undefined,
                });
            },
        }
    };
</script>

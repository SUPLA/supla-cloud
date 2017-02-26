<template>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ $t('Start date') }}</label>
                <div class="input-group date">
                    <input type="text" class="form-control datetimepicker-start" ref="startDatePicker">
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
                    <input type="text" class="form-control datetimepicker-end" ref="endDatePicker">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import Vue from "vue";
    import {mapState, mapActions} from "vuex";
    import moment from "moment";

    export default {
        name: 'schedule-form-start-end-date',
        mounted(){
            let startDatePicker = $(this.$refs.startDatePicker);
            let endDatePicker = $(this.$refs.endDatePicker);
            startDatePicker.datetimepicker({
                minDate: 'now',
                locale: Vue.config.lang,
                stepping: 5
            });
            endDatePicker.datetimepicker({
                useCurrent: false,
                locale: Vue.config.lang,
                stepping: 5
            });
            startDatePicker.on("dp.change", (e) => {
                endDatePicker.data("DateTimePicker").minDate(e.date);
                this.updateDateStart(moment(e.date ? e.date : undefined).startOf('minute').subtract(1, 'minute')); // minus to make it inclusive
            });
            endDatePicker.on("dp.change", (e) => {
                startDatePicker.data("DateTimePicker").maxDate(e.date);
                if (e.date) {
                    this.updateDateEnd(moment(e.date).startOf('minute'));
                } else {
                    this.updateDateEnd(undefined);
                }
            });
            if (this.dateStart) {
                startDatePicker.data('DateTimePicker').date(moment(this.dateStart).toDate());
            }
            if (this.dateEnd) {
                endDatePicker.data('DateTimePicker').date(moment(this.dateEnd).toDate());
            }
        },
        computed: mapState(['dateStart', 'dateEnd']),
        methods: mapActions(['updateDateStart', 'updateDateEnd']),
    }
</script>

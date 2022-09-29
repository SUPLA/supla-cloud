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
    import moment from "moment";
    import {Namespace, TempusDominus} from "@eonasdan/tempus-dominus";
    import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";

    export default {
        props: ['value'],
        data() {
            return {
                pickerStart: undefined,
                pickerEnd: undefined,
                dateStart: moment(),
                dateEnd: undefined,
                ready: false,
            };
        },
        mounted() {
            this.pickerStart = new TempusDominus(this.$refs.startDatePicker, {
                restrictions: {
                    minDate: moment().subtract(1, 'minute').toDate(),
                },
                localization: {
                    locale: this.$i18n.locale,
                },
                stepping: 1,
                useCurrent: false,
                display: {
                    icons: {
                        type: 'icons',
                        time: 'pe-7s-clock',
                        date: 'glyphicon glyphicon-calendar',
                        up: 'glyphicon glyphicon-chevron-up',
                        down: 'glyphicon glyphicon-chevron-down',
                        previous: 'glyphicon glyphicon-chevron-left',
                        next: 'glyphicon glyphicon-chevron-right',
                        today: 'fa-solid fa-calendar-check',
                        clear: 'glyphicon glyphicon-trash',
                        close: 'glyphicon glyphicon-remove'
                    },
                }
            });

            this.pickerEnd = new TempusDominus(this.$refs.endDatePicker, {
                restrictions: {
                    minDate: moment().add(5, 'minute').toDate(),
                },
                localization: {
                    locale: this.$i18n.locale,
                },
                stepping: 1,
                useCurrent: false,
            });

            this.pickerStart.subscribe(Namespace.events.change, () => {
                const date = this.pickerStart.viewDate;
                this.pickerEnd.updateOptions({restrictions: {minDate: date}});
                if (date) {
                    this.dateStart = moment(date).startOf('minute');
                } else {
                    this.dateStart = undefined;
                }
                this.onChange();
            });

            this.pickerEnd.subscribe(Namespace.events.change, () => {
                const date = this.pickerEnd.viewDate;
                this.pickerStart.updateOptions({restrictions: {maxDate: date}});
                if (date) {
                    this.dateEnd = moment(date).startOf('minute');
                } else {
                    this.dateEnd = undefined;
                }
                this.onChange();
            });

            if (this.value && this.value.dateStart) {
                this.pickerStart.dates.setFromInput(moment(this.value.dateStart).toDate());
            }
            if (this.value && this.value.dateEnd) {
                this.pickerEnd.dates.setFromInput(moment(this.value.dateEnd).toDate());
            }

            this.ready = true;
        },
        beforeDestroy() {
            this.pickerStart?.dispose();
            this.pickerEnd?.dispose();
        },
        methods: {
            onChange() {
                if (!this.ready) {
                    return;
                }
                this.$emit('input', {
                    dateStart: this.dateStart ? this.dateStart.format() : undefined,
                    dateEnd: this.dateEnd ? this.dateEnd.format() : undefined,
                });
            },
        }
    };
</script>

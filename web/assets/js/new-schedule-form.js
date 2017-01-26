function roundTime(time) {
    var parts = time.split(':');
    if (parts.length != 2) {
        return '0:0';
    }
    parts[1] = Math.min(Math.round(parts[1] / 5) * 5, 55);
    if (parts[1] < 10) parts[1] = '0' + parts[1];
    return parts.join(':');
}

function roundTo5(int) {
    return Math.round(Math.floor(int / 5) * 5);
}

var app = new Vue({
    el: '#new-schedule-form',
    delimiters: ['${', '}'], // defaults conflict with twig delimiters, http://stackoverflow.com/a/33935750/878514
    directives: {
        'schedule-chooser-once': {
            bind: function (element) {
                var time, date;
                var updateExpression = function () {
                    if (time && date) {
                        var timeParts = time.split(':');
                        var dateParts = date.split(' ');
                        var cronExpression = [roundTo5(timeParts[1]), roundTo5(timeParts[0]), dateParts[0], dateParts[1], '*', dateParts[2]].join(' ');
                        app.updateCronExpression(cronExpression);
                    }
                };
                $(element).find('.clockpicker').clockpicker().find('input').change(function () {
                    time = this.value = roundTime(this.value);
                    updateExpression();
                });
                $(element).find('.datepicker').datepicker({
                    language: LOCALE,
                    startDate: moment().toDate(),
                    format: 'd m yyyy'
                })
                    .on('changeDate', function () {
                        date = $(this).datepicker('getFormattedDate');
                        updateExpression();
                    });
            }
        },
        'schedule-chooser-minutely': {
            bind: function (element) {
                $(element).find('input[type=number]').change(function () {
                    this.value = Math.min(Math.max(roundTo5(this.value), 5), 30);
                    var cronExpression = '*/' + this.value + ' * * * *';
                    app.updateCronExpression(cronExpression);
                });
            }
        },
        'schedule-chooser-hourly': {
            bind: function (element) {
                var updateExpression = function () {
                    var chosenHours = $(element).find("input:checked");
                    if (chosenHours.length) {
                        chosenHours = chosenHours.map(function () {
                            return this.value
                        }).toArray();
                        var minute = $(element).find('input[type=number]').val() || 0;
                        var cronExpression = minute + ' ' + chosenHours.join(',') + ' * * *';
                        app.updateCronExpression(cronExpression);
                    }
                };
                $(element).find('input[type=checkbox], input[type=number]').change(updateExpression);
            }
        },
        'schedule-chooser-daily': {
            bind: function (element) {
                var time;
                var updateExpression = function () {
                    if (time) {
                        var weekdays = '*';
                        var chosenWeekdays = $(element).find('input:checked');
                        if (chosenWeekdays.length > 0 && chosenWeekdays.length < 7) {
                            weekdays = chosenWeekdays.map(function () {
                                return this.value
                            }).toArray().join(',');
                        }
                        var timeParts = time.split(':');
                        var cronExpression = [roundTo5(timeParts[1]), roundTo5(timeParts[0]), '*', '*', weekdays].join(' ');
                        app.updateCronExpression(cronExpression);
                    }
                }
                $(element).find('.clockpicker').clockpicker().find('input').change(function () {
                    time = this.value = roundTime(this.value);
                    updateExpression();
                });
                $(element).find('input[type=checkbox]').change(updateExpression);
            }
        }
    },
    data: {
        scheduleMode: undefined,
        cronExpression: '',
        channel: undefined,
        action: undefined,
        actionParam: undefined,
        nextRunDates: [],
        calculatingNextRunDates: false,
        submitting: false,
        channelFunctionMap: {},
        cronExpressionChangedInMeantime: false,
        dateStart: moment(),
        dateEnd: undefined
    },
    mounted: function () {
        this.channelFunctionMap = CHANNEL_FUNCTION_MAP;
        $('#new-schedule-form-loading').remove();
        $('#new-schedule-form').show();
        var self = this;
        $(".colorpicker").spectrum({
            color: '#F00',
            showButtons: false
        }).change(function () {
            var hue = this.value.match(/^hsv\(([0-9]+)/)[1]
            self.actionParam = hue;
        });
        var updateStartDate = function () {
            var time = roundTime($('.clockpicker-start').find('input').val()).split(':');
            var date = $('.datepicker-start').datepicker('getDate');
            self.dateStart = moment(date ? date : undefined).startOf('minute').set('hour', time[0]).set('minute', time[1]).subtract(1, 'minute'); // minus to make it inclusive
            self.updateCronExpression(self.cronExpression);
        };
        var updateEndDate = function () {
            var date = $('.datepicker-end').datepicker('getDate');
            if (date) {
                var time = roundTime($('.clockpicker-end').find('input').val()).split(':');
                self.dateEnd = moment(date ? date : undefined).startOf('minute').set('hour', time[0]).set('minute', time[1]);
            } else {
                self.dateEnd = undefined;
            }
            self.updateCronExpression(self.cronExpression);
        };
        $('.clockpicker-start').clockpicker().find('input').change(updateStartDate).val(roundTime(moment().format('H:mm')));
        $('.clockpicker-end').clockpicker().find('input').change(updateEndDate);
        $('.datepicker-start, .datepicker-end').datepicker({
            autoclose: true,
            language: LOCALE,
            startDate: moment().toDate()
        });
        $('.datepicker-start').on('changeDate', function () {
            updateStartDate();
            $('.datepicker-end').datepicker('setStartDate', $(this).datepicker('getDate'));
        });
        $('.datepicker-end').on('changeDate', function () {
            $('.datepicker-start').datepicker('setEndDate', $(this).datepicker('getDate'));
        });
        $('.datepicker-start').datepicker('update', moment().toDate());
    }
    ,
    methods: {
        chooseMode: function (mode) {
            this.scheduleMode = mode;
            this.nextRunDates = [];
            this.cronExpression = '';
        },
        updateCronExpression: function (cronExpression) {
            var self = this;
            this.cronExpression = cronExpression;
            if (!this.calculatingNextRunDates && cronExpression) {
                this.calculatingNextRunDates = true;
                $.post(BASE_URL + 'schedule/next-run-dates', {
                    cronExpression: cronExpression,
                    dateStart: this.dateStart ? this.dateStart.format() : '',
                    dateEnd: this.dateEnd ? this.dateEnd.format(): ''
                }).then(function (response) {
                    self.nextRunDates = response.nextRunDates.map(function (dateString) {
                        return {
                            date: moment(dateString).format('LLL'),
                            fromNow: moment(dateString).fromNow()
                        }
                    });
                }).always(function () {
                    self.calculatingNextRunDates = false;
                    if (self.cronExpressionChangedInMeantime) {
                        self.cronExpressionChangedInMeantime = false;
                        self.updateCronExpression(self.cronExpression);
                    }
                });
            } else {
                this.cronExpressionChangedInMeantime = true;
            }
        },
        submit: function () {
            var self = this;
            this.submitting = true;
            $.post(BASE_URL + 'schedule/create', {
                cronExpression: this.cronExpression,
                action: this.action,
                actionParam: this.actionParam,
                mode: this.scheduleMode,
                channel: this.channel,
                dateStart: this.dateStart ? this.dateStart.format() : '',
                dateEnd: this.dateEnd ? this.dateEnd.format() : ''
            }).always(function () {
                self.submitting = false;
            });
        }
    }
});

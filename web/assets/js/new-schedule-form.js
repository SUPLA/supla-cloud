function roundTime(time) {
    var parts = time.split(':');
    parts[1] = Math.min(Math.round(parts[1] / 5) * 5, 55);
    if (parts[1] < 10) parts[1] = '0' + parts[1];
    return parts.join(':');
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
                        var cronExpression = [Math.round(timeParts[1]), Math.round(timeParts[0]), dateParts[0], dateParts[1], '*', dateParts[2]].join(' ');
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
                    this.value = Math.min(Math.max(Math.floor(this.value / 5) * 5, 5), 30);
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
                        var cronExpression = [timeParts[1], timeParts[0], '*', '*', weekdays].join(' ');
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
        scheduleName: '',
        nextRunDates: [],
        calculatingNextRunDates: false,
        submitting: false,
        channelFunctionMap: {},
        cronExpressionChangedInMeantime: false
    },
    mounted: function () {
        this.channelFunctionMap = CHANNEL_FUNCTION_MAP;
        $('#new-schedule-form-loading').remove();
        $('#new-schedule-form').show();
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
            if (!this.calculatingNextRunDates) {
                this.calculatingNextRunDates = true;
                $.getJSON(BASE_URL + 'schedule/next-run-dates/' + cronExpression).then(function (response) {
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
                name: this.scheduleName,
                cronExpression: this.cronExpression,
                action: this.action,
                channel: this.channel
            }).then(function () {
                self.submitting = false;
            });
        }
    }
});

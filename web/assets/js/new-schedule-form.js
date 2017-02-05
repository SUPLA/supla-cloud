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
    components: {
        'timezone-chooser': {
            data: function () {
                return {
                    editingTimezone: false,
                    userTimezone: undefined
                };
            },
            mounted: function () {
                if (TIMEZONE) {
                    this.userTimezone = TIMEZONE;
                    moment.tz.setDefault(this.userTimezone);
                } else {
                    this.userTimezone = moment.tz.guess();
                    this.chooseTimezone();
                }
            },
            methods: {
                editTimezone: function () {
                    this.editingTimezone = true;
                },
                chooseTimezone: function () {
                    this.editingTimezone = false;
                    moment.tz.setDefault(this.userTimezone);
                    $.ajax({
                        method: 'PUT',
                        data: {timezone: this.userTimezone},
                        url: BASE_URL + 'schedule/user-timezone'
                    })
                },
                getAvailableTimezones: function () {
                    return moment.tz.names().filter(function (timezone) {
                        return timezone.match(/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//);
                    }).map(function (timezone) {
                        return {
                            name: timezone,
                            offset: moment.tz(timezone).utcOffset() / 60,
                            currentTime: moment.tz(timezone).format('H:mm')
                        }
                    }).sort(function (timezone1, timezone2) {
                        if (timezone1.offset == timezone2.offset) {
                            return timezone1.name < timezone2.name ? -1 : 1;
                        } else {
                            return timezone1.offset - timezone2.offset
                        }
                    });
                }
            }
        }
    },
    directives: {
        'schedule-chooser-once': {
            bind: function (element) {
                var datepicker = $(element).find('.once-datepicker');
                datepicker.datetimepicker({
                    minDate: 'now',
                    locale: LOCALE,
                    stepping: 5,
                    inline: true,
                    sideBySide: true
                });
                datepicker.on("dp.change", function (e) {
                    var cronExpression = moment(e.date).format('m H D M * Y');
                    app.updateCronExpression(cronExpression);
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
                        var timeParts = roundTime(time).split(':');
                        var cronExpression = [timeParts[1], timeParts[0], '*', '*', weekdays].join(' ');
                        app.updateCronExpression(cronExpression);
                    }
                }
                $(element).find('.clockpicker').datetimepicker({
                    minDate: 'now',
                    format: 'LT',
                    inline: true,
                    locale: LOCALE,
                    stepping: 5
                }).on("dp.change", function (e) {
                    time = moment(e.date).format('H:m');
                    updateExpression();
                });
                $(element).find('input[type=checkbox]').change(updateExpression);
            }
        }
    },
    data: {
        scheduleMode: 'once',
        cronExpression: '',
        channel: undefined,
        action: undefined,
        actionParam: undefined,
        nextRunDates: [],
        calculatingNextRunDates: false,
        submitting: false,
        channelFunctionMap: {},
        cronExpressionChangedInMeantime: false,
        dateStart: undefined,
        dateEnd: undefined,
        modeVariables: {
            hourly: {
                minute: 0
            }
        }
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
        $('.datetimepicker-start').datetimepicker({
            minDate: 'now',
            locale: LOCALE,
            stepping: 5
        });
        $('.datetimepicker-end').datetimepicker({
            useCurrent: false,
            locale: LOCALE,
            stepping: 5
        });
        $(".datetimepicker-start").on("dp.change", function (e) {
            $('.datetimepicker-end').data("DateTimePicker").minDate(e.date);
            self.dateStart = moment(e.date ? e.date : undefined).startOf('minute').subtract(1, 'minute'); // minus to make it inclusive
            self.updateCronExpression(self.cronExpression);
        });
        $(".datetimepicker-end").on("dp.change", function (e) {
            $('.datetimepicker-start').data("DateTimePicker").maxDate(e.date);
            if (e.date) {
                self.dateEnd = moment(e.date).startOf('minute');
            } else {
                self.dateEnd = undefined;
            }
            self.updateCronExpression(self.cronExpression);
        });
    },
    methods: {
        chooseMode: function (mode) {
            this.scheduleMode = mode;
            this.nextRunDates = [];
            this.cronExpression = '';
            if (!this.dateStart) {
                this.dateStart = moment();
            }
        },
        updateCronExpression: function (cronExpression) {
            var self = this;
            this.cronExpression = cronExpression;
            if (!this.calculatingNextRunDates && cronExpression) {
                this.calculatingNextRunDates = true;
                $.post(BASE_URL + 'schedule/next-run-dates', {
                    cronExpression: cronExpression,
                    dateStart: this.dateStart ? this.dateStart.format() : '',
                    dateEnd: this.dateEnd ? this.dateEnd.format() : ''
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
            }).done(function (schedule) {
                window.location.href = BASE_URL + 'schedule/' + schedule.id;
            }).fail(function (response) {
                alert(response.responseJSON);
            }).always(function () {
                self.submitting = false;
            });
        },
        prepend0IfLessThan10: function (value) {
            return value < 10 ? '0' + value : value;
        }
    }
});

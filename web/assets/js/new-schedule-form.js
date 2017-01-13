$(function () {
    $('.datetimepicker').datetimepicker({
        locale: 'pl',
        inline: true,
        sideBySide: true,
        defaultDate: moment().add(2, 'hour').startOf('hour'),
        minDate: moment().add(5, 'minute'),
        maxDate: moment().add(5, 'year'),
        stepping: 5
    });
    $('.timepicker').datetimepicker({
        format: 'LT',
        locale: 'pl',
        inline: true,
        // sideBySide: true,
        // defaultDate: moment().add(2, 'hour').startOf('hour'),
        // minDate: moment().add(5, 'minute'),
        // maxDate: moment().add(5, 'year'),
        stepping: 5
    });
});

new Vue({
    el: '#new-schedule-form',
    delimiters: ['${', '}'], // defaults conflict with twig delimiters, http://stackoverflow.com/a/33935750/878514
    data: {
        scheduleMode: undefined,
        cronExpression: '',
        action: undefined,
        nextRunDates: [],
        calculatingNextRunDates: false
    },
    methods: {
        chooseMode: function (mode) {
        },
        updateCronExpression: function (mode, cronExpression) {
            var self = this;
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
            });
        },
        renderDropdowns: function () {
            setTimeout(function () {
                $('.selectpicker').selectpicker();
                $('.selectpicker').selectpicker('refresh');
            }, 50);
        }
    }
});
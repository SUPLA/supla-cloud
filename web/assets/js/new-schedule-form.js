$(function () {
    // $('.datetimepicker2').datetimepicker({
    //     // locale: 'pl',
    //     inline: true,
    //     sideBySide: true,
    //     // defaultDate: moment().add(2, 'hour').startOf('hour'),
    //     // minDate: moment().add(5, 'minute'),
    //     // maxDate: moment().add(5, 'year'),
    //     // stepping: 5
    // });
    // $('.timepicker').datetimepicker({
    //     format: 'LT',
    //     locale: 'pl',
    //     inline: true,
    //     // sideBySide: true,
    //     // defaultDate: moment().add(2, 'hour').startOf('hour'),
    //     // minDate: moment().add(5, 'minute'),
    //     // maxDate: moment().add(5, 'year'),
    //     stepping: 5
    // });

});

new Vue({
    el: '#new-schedule-form',
    delimiters: ['${', '}'], // defaults conflict with twig delimiters, http://stackoverflow.com/a/33935750/878514
    data: {
        scheduleMode: undefined,
        cronExpression: '',
        channel: undefined,
        action: undefined,
        nextRunDates: [],
        calculatingNextRunDates: false,
        submitting: false
    },
    methods: {
        chooseMode: function (mode) {
            this.scheduleMode = mode;
            this.nextRunDates = [];
            this.cronExpression = '';
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
        submit: function () {
            this.submitting = true;
            $.post(BASE_URL + 'schedule', {
                cronExpression: this.cronExpression,
                action: this.action,
                channel: this.channel
            }).then();
        },
        renderDropdowns: function () {
            setTimeout(function () {
                $('.selectpicker').selectpicker();
                $('.selectpicker').selectpicker('refresh');
            }, 50);
        }
    }
});

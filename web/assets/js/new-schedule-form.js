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
        location: undefined,
        device: undefined,
        channel: undefined,
        action: undefined,
        cronExpression: ''
        // locations: locations
    },
    methods: {
        chooseMode: function (mode) {

        },
        renderDropdowns: function () {
            setTimeout(function () {
                $('.selectpicker').selectpicker();
                $('.selectpicker').selectpicker('refresh');
            }, 50);
        }
    }
});
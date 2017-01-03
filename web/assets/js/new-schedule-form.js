$(function () {
    // $('.datetimepicker').datetimepicker({
    //     locale: 'pl'
    // });
});

new Vue({
    el: '#new-schedule-form',
    delimiters: ['${', '}'], // defaults conflict with twig delimiters, http://stackoverflow.com/a/33935750/878514
    data: {
        scheduleMode: undefined,
    }
});
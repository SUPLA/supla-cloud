(function () {
    // idea based on http://stackoverflow.com/a/4029518/878514
    var LOGOUT_AFTER_IDLE_MINUTES = 15;
    $(document).ready(function () {
        var idleTime = 0;
        var idleWarningShown = false;
        var idleInterval = setInterval(function () {
            idleTime = idleTime + 1;
            if (idleTime == LOGOUT_AFTER_IDLE_MINUTES - 1) {
                idleWarningShown = true;
                $('#idleWarningModal').modal({});
            }
            else if (idleTime >= LOGOUT_AFTER_IDLE_MINUTES) {
                $('#idleWarningModal').modal('hide');
                $("#logout").click();
                clearInterval(idleInterval);
            }
        }, 60000);
        var clearIdleTime = function () {
            idleTime = 0;
            if (idleWarningShown) {
                idleWarningShown = false;
                $('#idleWarningModal').modal('hide');
            }
        }
        $(this).click(clearIdleTime);
        $(this).keypress(clearIdleTime);
    });
})();

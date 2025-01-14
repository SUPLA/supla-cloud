const fs = require('fs');
const path = require('path');

module.exports = (on) => {
    on('task', {
        dumpBackendLogs() {
            const today = new Date().toISOString().slice(0, 10);
            fs.readFile(path.join(__dirname, `/../../../../../var/logs/e2e-${today}.log`), 'utf8', (err, data) => {
                if (err) {
                    console.error(err);
                } else {
                    console.log(data);
                }
            });
            return null
        },
    });
};

const ms = require('smtp-tester')

module.exports = (on) => {
    const port = 7777;
    const mailServer = ms.init(port);
    const lastEmail = {};
    console.log('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABB');
    mailServer.bind((addr, id, email) => {
        console.log('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
        lastEmail[email.headers.to] = email;
    });
    on('task', {
        getLastEmail(email) {
            return lastEmail[email] || null;
        },
    });
};

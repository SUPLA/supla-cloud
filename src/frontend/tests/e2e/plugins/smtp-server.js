import * as ms from 'smtp-tester';

export default (on) => {
  const port = 7777;
  const mailServer = ms.init(port);
  const lastEmail = {};
  mailServer.bind((addr, id, email) => {
    lastEmail[email.headers.to] = email;
  });
  on('task', {
    getLastEmail(email) {
      return lastEmail[email] || null;
    },
  });
};

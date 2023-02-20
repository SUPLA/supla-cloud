// const path = require('path');
const mysql = require('mysql');

// const defaultConfig = {
//     database: 'mongodb://localhost:4001/meteor',
//     dropDatabase: true,
// };

module.exports = (on, config) => {
    on('task', {
        async 'seed:database'() {
            const connection = mysql.createConnection({
                host: 'localhost',
                user: 'root',
                password: 'php',
                database: 'supla_test',
            });
            connection.connect();
            return new Promise((resolve, reject) => {
                connection.query('SHOW TABLES', (error, results) => {
                    if (error) {
                        reject(error);
                    } else {
                        connection.end();
                        console.log(results)
                        return resolve(results);
                    }
                });
            });
        },
    });
    return config;
};

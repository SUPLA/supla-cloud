const mysql = require('mysql');
const {ConnectionString} = require('connection-string');

module.exports = (on, config) => {
    on('task', {
        async 'seed:database'() {
            const connectionString = config.env.DATABASE;
            const connectionConfig = new ConnectionString(connectionString);
            console.log(connectionConfig);
            const connection = mysql.createConnection({
                host: connectionConfig.host,
                user: connectionConfig.user,
                port: connectionConfig.port || 3306,
                password: connectionConfig.password,
                database: connectionConfig.path ? connectionConfig.path[0] : 'supla_test',
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

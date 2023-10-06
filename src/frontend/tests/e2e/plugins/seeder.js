const mysql = require('mysql');
const {ConnectionString} = require('connection-string');
const path = require("path");
const Importer = require('mysql-import');

module.exports = (on, config) => {
    on('task', {
        async 'seed:database'(fixtures) {
            const connectionString = config.env.DATABASE;
            const connectionConfig = new ConnectionString(connectionString);
            const database = connectionConfig.path ? connectionConfig.path[0] : 'supla_e2e';
            const connection = mysql.createConnection({
                host: connectionConfig.host,
                user: connectionConfig.user,
                port: connectionConfig.port || 3306,
                password: connectionConfig.password,
                multipleStatements: true,
            });
            connection.connect();
            return new Promise((resolve, reject) => {
                connection.query(`DROP SCHEMA IF EXISTS ${database}; CREATE SCHEMA ${database}; USE ${database};`, async (error) => {
                    connection.end();
                    if (error) {
                        reject(error);
                    } else {
                        const importer = new Importer({
                            host: connectionConfig.host,
                            user: connectionConfig.user,
                            password: connectionConfig.password,
                            database
                        });
                        const additionalFixtures = fixtures ? (Array.isArray(fixtures) ? fixtures : [fixtures]) : [];
                        const fixtureFiles = ['schema.sql', '00_required.sql', ...additionalFixtures];
                        for (const fileToImport of fixtureFiles) {
                            const fullPath = path.resolve(`./tests/e2e/plugins/data/${fileToImport}`);
                            await importer.import(fullPath);
                        }
                        resolve(true);
                    }
                });
            });
        },
        async sql(query) {
            const connectionString = config.env.DATABASE;
            const connectionConfig = new ConnectionString(connectionString);
            const database = connectionConfig.path ? connectionConfig.path[0] : 'supla_e2e';
            const connection = mysql.createConnection({
                host: connectionConfig.host,
                user: connectionConfig.user,
                port: connectionConfig.port || 3306,
                database,
                password: connectionConfig.password,
                multipleStatements: true,
            });
            connection.connect();
            return new Promise((resolve, reject) => {
                connection.query(query, async (error, result) => {
                    connection.end();
                    if (error) {
                        reject(error);
                    } else {
                        resolve(result);
                    }
                });
            });
        },
    });
    return config;
};

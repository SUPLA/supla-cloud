import {ConnectionString} from "connection-string";
import * as mysql from "mysql2";
import * as path from "node:path";
import * as fs from "node:fs";

export default (on, config) => {
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
                    if (error) {
                        reject(error);
                    } else {
                        const additionalFixtures = fixtures ? (Array.isArray(fixtures) ? fixtures : [fixtures]) : [];
                        const fixtureFiles = ['schema.sql', '00_required.sql', ...additionalFixtures];
                        for (const fileToImport of fixtureFiles) {
                            const fullPath = path.resolve(`./tests/e2e/plugins/data/${fileToImport}`);
                            const sqlContent = fs.readFileSync(fullPath, 'utf8');
                            const sqlFixed = sqlContent.toString().replace(/DELIMITER ;?;/gm, '').replace(/;;/gm, ';')
                            await new Promise((resolveQuery, rejectQuery) => {
                                connection.query(sqlFixed, (error) => {
                                    if (error) {
                                        rejectQuery(error);
                                    } else {
                                        resolveQuery();
                                    }
                                });
                            });
                        }
                        connection.end();
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

import {ConnectionString} from 'connection-string';
import * as mysql from 'mysql2';
import * as path from 'node:path';
import * as fs from 'node:fs';
import {execFile} from 'node:child_process';
import {promisify} from 'node:util';

export default (on, config) => {
  on('task', {
    async 'seed:database'(fixtures) {
      const connectionString = config.env.DATABASE;
      const connectionConfig = new ConnectionString(connectionString);
      const database = connectionConfig.path ? connectionConfig.path[0] : 'supla_e2e';
      if (!/^[a-zA-Z0-9_]+_e2e$/.test(database)) {
        throw new Error('Cypress database must end with _e2e and contain only letters, digits or underscores');
      }
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
              const sqlFixed = sqlContent
                .toString()
                .replace(/DELIMITER ;?;/gm, '')
                .replace(/;;/gm, ';');
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
            // The SQL fixture dump predates SupLAN. Install the actual final DDL, not preview compatibility tables.
            // Use e2e env with its suffix to target exactly the database just seeded above.
            try {
              await promisify(execFile)(
                'php',
                [
                  '../bin/console',
                  'doctrine:migrations:execute',
                  'SuplaBundle\\Migrations\\Migration\\Version20261007104248',
                  'SuplaBundle\\Migrations\\Migration\\Version20261011120000',
                  'SuplaBundle\\Migrations\\Migration\\Version20261016120000',
                  '--up',
                  '--no-interaction',
                  '--env=e2e',
                ],
                {
                  env: {
                    ...process.env,
                    DATABASE_HOST: connectionConfig.host,
                    DATABASE_PORT: String(connectionConfig.port || 3306),
                    DATABASE_NAME: database.slice(0, -4),
                    DATABASE_USER: connectionConfig.user,
                    DATABASE_PASSWORD: connectionConfig.password || '',
                  },
                }
              );
              resolve(true);
            } catch (error) {
              reject(error);
            }
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

import * as fs from 'node:fs';
import * as path from 'node:path';

const ROOT_DIRS = ['../../config', '../../src'];

const DESCRIPTIONS = {
  APP_ENV:
    'The application environment. Must be set to `prod` in deployment. See more at [Configuring Symfony](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment).',
  APP_DEBUG: 'Whether to enable debug mode or not. Must be set to `0` in deployment.',
  APP_SECRET:
    'Random, at least 32-chars, at best 64-chars length secret for the application. Must be set in deployment. You may generate it with `openssl rand -hex 32`.',
  DATABASE_URL: 'Database connection string. Example: `mysql://root:php@127.0.0.1:3306/supla?serverVersion=mariadb-11.7.2&charset=utf8mb4`.',
  SUPLA_HOST_ADDRESS: 'Host (and possibly port) where SUPLA Cloud is running. E.g. `svr44.supla.org` or `mycloudinstance.local:8080`.',
  MAILER_DSN:
    'Mailer DSN connection string.<br> For Gmail as a transport, use: `gmail://username:password@localhost`<br>' +
    'For a generic SMTP server, use: `smtp://localhost:25?encryption=&auth_mode=`<br>' +
    'With username and password, use: `smtp://username:password@localhost:25?encryption=&auth_mode=`',
  SUPLA_MAILER_FROM: 'From field for emails sent by SUPLA Cloud.',
  SUPLA_ADMIN_EMAIL: 'Email address of the admin user.',
  CORS_ALLOW_ORIGIN: 'Regex for hosts that should be enabled for CORS requests.',
  DATABASE_TSDB_URL: 'Database connection string for TimescaleDB, if used.',
  GOOGLE_RECAPTCHA_SITE_KEY: 'Public site key for Google reCAPTCHA. Must be set in deployment if you want to use reCAPTCHA.',
  GOOGLE_RECAPTCHA_SECRET: 'Secret key for Google reCAPTCHA. Must be set in deployment if you want to use reCAPTCHA.',
  SUPLA_ACCOUNTS_REGISTRATION_ENABLED: 'Whether accounts registration is enabled or not. Use `true` or `false`.',
  SUPLA_TOKEN_LIFETIME_WEBAPP: 'Duration in seconds for which access tokens are valid for web applications (the session length).',
  SUPLA_ACT_AS_BROKER_CLOUD: 'Whether the server should work as an official broker. Use `true` or `false`.',
  SUPLA_API_RATE_LIMIT_ENABLED: 'Whether to enable API rate limiting. Use `true` or `false`.',
  SUPLA_API_RATE_LIMIT_GLOBAL_LIMIT: 'Global limit for API requests (if used). Use format `requests/seconds`.',
  SUPLA_API_RATE_LIMIT_USER_DEFAULT_LIMIT: 'Default per-user limit for API requests (if used). Use format `requests/seconds`.',
  SUPLA_BRUTE_FORCE_AUTH_PREVENTION_ENABLED:
    'Whether to enable brute force prevention for authentication (blocking user for some time after unsuccessful login attempts). Use `true` or `false`.',
  SUPLA_MQTT_BROKER_ENABLED: 'Whether to enable MQTT broker. Use `true` or `false`.',
  SUPLA_MQTT_BROKER_HOST: 'MQTT broker host.',
  SUPLA_MQTT_BROKER_PORT: 'MQTT broker port.',
  SUPLA_MQTT_BROKER_TLS: 'Whether to use TLS encryption for MQTT broker. Use `true` or `false`.',
  SUPLA_MQTT_BROKER_USERNAME: 'MQTT broker username.',
  SUPLA_MQTT_BROKER_PASSWORD: 'MQTT broker password.',
  SUPLA_MQTT_BROKER_INTEGRATED_AUTH: 'Whether to use integrated authentication for MQTT broker. Use `true` or `false`.',
  SUPLA_MQTT_BROKER_CLIENT_ID: 'MQTT broker client ID.',
  SUPLA_PROTOCOL: 'Protocol that should be used for web application and API. Must be `http` or `https`.',
  SUPLA_REQUIRE_COOKIE_POLICY_ACCEPTANCE: 'Whether to require cookie policy acceptance. Use `true` or `false`.',
  SUPLA_REQUIRE_REGULATIONS_ACCEPTANCE: 'Whether to require regulations acceptance. Use `true` or `false`.',
};

const envUsageRegex = /%env\(([^)]+)\)%/g;
const envDefaultRegex = /env\(([^)]+)\):\s*['"]?([^'"\n]*)['"]?/g;

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);

  list.forEach((file) => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);

    if (stat && stat.isDirectory()) {
      results = results.concat(walk(fullPath));
    } else if (file.endsWith('.yaml') || file.endsWith('.yml') || file.endsWith('.php')) {
      results.push(fullPath);
    }
  });

  return results;
}

function extractEnvUsage(content) {
  const vars = new Set();
  let match;

  while ((match = envUsageRegex.exec(content)) !== null) {
    const inside = match[1];
    const parts = inside.split(':');
    const envName = parts[parts.length - 1];

    if (/^[A-Z0-9_]+$/.test(envName)) {
      vars.add(envName);
    }
  }

  return vars;
}

function extractEnvDefaults(content) {
  const defaults = {};
  let match;

  while ((match = envDefaultRegex.exec(content)) !== null) {
    const name = match[1];
    const value = match[2];
    defaults[name] = value;
  }

  return defaults;
}

// === MAIN ===

let allEnvVars = new Set();
let defaultsMap = {};

for (const dir of ROOT_DIRS) {
  if (!fs.existsSync(dir)) continue;

  const files = walk(dir);

  for (const file of files) {
    const content = fs.readFileSync(file, 'utf8');

    const vars = extractEnvUsage(content);
    vars.forEach((v) => allEnvVars.add(v));

    const defs = extractEnvDefaults(content);
    Object.assign(defaultsMap, defs);
  }
}

// === SPLIT ===

const required = [];
const optional = [];

for (const envVar of allEnvVars) {
  if (Object.prototype.hasOwnProperty.call(defaultsMap, envVar)) {
    optional.push(envVar);
  } else {
    required.push(envVar);
  }
}

required.sort();
optional.sort();
required.unshift('APP_DEBUG');
required.unshift('APP_ENV');
optional.push('SUPLA_MQTT_BROKER_CLIENT_ID');
defaultsMap['SUPLA_MQTT_BROKER_CLIENT_ID'] = '';

// === SORT BY DESCRIPTIONS ORDER ===

function sortByDescriptionsOrder(vars) {
  const descriptionsKeys = Object.keys(DESCRIPTIONS);
  return vars.sort((a, b) => {
    const indexA = descriptionsKeys.indexOf(a);
    const indexB = descriptionsKeys.indexOf(b);
    if (indexA !== -1 && indexB !== -1) {
      return indexA - indexB;
    }
    if (indexA !== -1) {
      return -1;
    }
    if (indexB !== -1) {
      return 1;
    }
    return a.localeCompare(b);
  });
}

sortByDescriptionsOrder(required);
sortByDescriptionsOrder(optional);

function formatDefault(value) {
  if (value === '') return '"" (empty)';
  return value;
}

console.log('# Environment Variables\n');

console.log('## Required variables\n');
console.log('| Variable | Description |');
console.log('|----------|-------------|');

required.forEach((v) => {
  console.log(`| \`${v}\` | ${DESCRIPTIONS[v] ?? ''} |`);
});

console.log('\n## Optional variables (with defaults)\n');
console.log('| Variable | Default | Description |');
console.log('|----------|---------|-------------|');

optional.forEach((v) => {
  const def = formatDefault(defaultsMap[v]);
  console.log(`| \`${v}\` | ${def.replace(/\|/g, '\\|')} | ${DESCRIPTIONS[v] ?? ''} |`);
});

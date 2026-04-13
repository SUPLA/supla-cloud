import * as fs from 'node:fs';
import * as path from 'node:path';

const ROOT_DIRS = ['../../config', '../../src'];

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
  if (defaultsMap.hasOwnProperty(envVar)) {
    optional.push(envVar);
  } else {
    required.push(envVar);
  }
}

required.sort();
optional.sort();
required.unshift('APP_ENV');

// === FORMAT DEFAULT ===

function formatDefault(value) {
  if (value === '') return '"" (empty)';
  return value;
}

// === OUTPUT ===

console.log('# Environment Variables\n');

// REQUIRED
console.log('## Required variables\n');
console.log('| Variable | Description |');
console.log('|----------|-------------|');

required.forEach((v) => {
  console.log(`| ${v} | |`);
});

// OPTIONAL
console.log('\n## Optional variables (with defaults)\n');
console.log('| Variable | Default | Description |');
console.log('|----------|---------|-------------|');

optional.forEach((v) => {
  const def = formatDefault(defaultsMap[v]);
  console.log(`| ${v} | ${def.replace(/\|/g, '\\|')} | |`);
});

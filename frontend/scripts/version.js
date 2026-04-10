import * as fs from 'node:fs';

let theVersion = process.env.RELEASE_VERSION;

const backendBuildConfigPath = '../config/build/version-meta.php';

if (!theVersion && fs.existsSync(backendBuildConfigPath)) {
  theVersion = fs
    .readFileSync(backendBuildConfigPath, 'utf8')
    .match(/version' => '(.+)'/)[1]
    .trim();
}

if (!theVersion) {
  theVersion = 'UNKNOWN_VERSION';
}

export const version = theVersion.replace(/^[v'"]+|['"]+$/g, '');

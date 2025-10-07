import {printAsciiLogoAndVersion} from "./logo.js";
import {version} from "./version.js";
import ora from "ora";
import {deleteAsync, deleteSync} from 'del';
import * as fs from "node:fs";
import * as async from 'async';
import * as child from 'child_process';
import chalk from "chalk";

process.chdir('../../');
console.log(process.cwd());

const releasePackageName = process.env.RELEASE_FILENAME || 'supla-cloud-v' + version + (process.env.NODE_ENV === 'development' ? '-dev' : '') + '.tar.gz';

printAsciiLogoAndVersion(version);

console.log('');
console.log("Preparing release package.");
console.log('');

async function start() {
    await clearVendorDirectory();
}

async function clearVendorDirectory() {
    const spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
    try {
        await deleteAsync('vendor/**/.git');
        spinner.succeed('Vendor directory cleaned.');
        await clearReleaseDirectory();
    } catch (error) {
        spinner.fail();
        console.log(error);
    }
}

async function clearReleaseDirectory() {
    const spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
    try {
        await deleteAsync('release');
        spinner.succeed('Release directory deleted.');
        copyToReleaseDirectory();
    } catch (error) {
        spinner.fail();
        console.error(error);
    }
}

function copyToReleaseDirectory() {
    const spinner = ora({text: 'Copying application files.', color: 'yellow'}).start();
    const calls = [];
    let directories = [
        'app/',
        'bin/',
        'src/SuplaBundle',
        'vendor/',
        'web/',
    ];
    if (process.env.NODE_ENV === 'development') {
        directories.push('src/SuplaDeveloperBundle');
    }
    directories.forEach(function (filename) {
        calls.push(function (callback) {
            fs.mkdirSync('release/' + filename, {recursive: true});
            fs.cp(filename, 'release/' + filename, {recursive: true}, function (err) {
                if (!err) {
                    callback(err);
                } else {
                    callback(null, filename);
                }
            });
        });
    });
    async.series(calls, function (err) {
        if (err) {
            spinner.fail();
            console.error(err);
        } else {
            createRequiredDirectories();
            copySingleRequiredFiles();
            clearLocalConfigFiles();
            spinner.succeed('Application files copied.');
            createZipArchive();
        }
    });
}

function createRequiredDirectories() {
    [
        'var',
        'var/cache',
        'var/local',
        'var/logs',
        'var/sessions',
    ].forEach(function (dirname) {
        fs.mkdirSync('release/' + dirname, {recursive: true});
    });
}

function copySingleRequiredFiles() {
    fs.copyFileSync('src/.htaccess', 'release/src/.htaccess');
    fs.copyFileSync('README.md', 'release/README.md');
    fs.copyFileSync('composer.json', 'release/composer.json');
}

function clearLocalConfigFiles() {
    let pathsToDelete = [
        'release/**/.gitignore',
        'release/app/config/config_local.yml',
        'release/app/config/config_test.yml',
        'release/app/config/parameters.yml',
        'release/app/config/parameters.yml.travis',
        'release/web/router.php',
        'release/web/assets/**/*.map',
    ];
    if (process.env.NODE_ENV !== 'development') {
        pathsToDelete.push('release/src/*/Tests');
        pathsToDelete.push('release/app/config/config_dev.yml');
        pathsToDelete.push('release/app/config/routing_dev.yml');
    }
    deleteSync(pathsToDelete);
}

function createZipArchive() {
    const spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
    child.exec('tar -czf ' + releasePackageName + ' -C release .', function (err) {
        if (err) {
            spinner.fail();
            console.log(err);
        } else {
            spinner.succeed('Release archive created.');
            console.log('');
            console.log("Package: " + chalk.green(releasePackageName));
            const fileSizeInBytes = fs.statSync(releasePackageName).size;
            console.log('Size: ' + Math.round(fileSizeInBytes / 1024) + 'kB (' + Math.round(fileSizeInBytes * 10 / 1024 / 1024) / 10 + 'MB)');
        }
    });
}

await start();

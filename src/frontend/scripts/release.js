const project = require("./logo");
const chalk = require('chalk');
const ora = require('ora');
const fs = require('fs-extra');
const async = require('async');
const del = require('del');
const exec = require('child_process').exec;
const version = require('./version').version;

process.chdir('../../');
console.log(process.cwd());

const releasePackageName = process.env.RELEASE_FILENAME || 'supla-cloud-v' + version + (process.env.NODE_ENV === 'development' ? '-dev' : '') + '.tar.gz';

project.printAsciiLogoAndVersion(version);

console.log('');
console.log("Preparing release package.");
console.log('');

function start() {
    clearVendorDirectory();
}

function clearVendorDirectory() {
    const spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
    del('vendor/**/.git')
        .then(() => {
            spinner.succeed('Vendor directory cleaned.');
            clearReleaseDirectory();
        })
        .catch((err) => {
            console.log(err);
            spinner.fail();
        });
}

function clearReleaseDirectory() {
    const spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
    fs.remove('release/', function (err) {
        if (err) {
            spinner.fail();
            console.error(err);
        } else {
            spinner.succeed('Release directory deleted.');
            copyToReleaseDirectory();
        }
    });
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
            fs.mkdirsSync('release/' + filename);
            fs.copy(filename, 'release/' + filename, function (err) {
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
        fs.mkdirsSync('release/' + dirname);
    });
}

function copySingleRequiredFiles() {
    fs.copySync('src/.htaccess', 'release/src/.htaccess');
    fs.copySync('README.md', 'release/README.md');
    fs.copySync('composer.json', 'release/composer.json');
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
    del.sync(pathsToDelete);
}

function createZipArchive() {
    const spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
    exec('tar -czf ' + releasePackageName + ' -C release .', function (err) {
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

start();

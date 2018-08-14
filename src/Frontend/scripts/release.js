var project = require("./logo");
var chalk = require('chalk');
const ora = require('ora');
var fs = require('fs-extra');
var path = require('path');
var async = require('async');
var del = require('del');
var exec = require('child_process').exec;

process.chdir('../../');
console.log(process.cwd());


var version = require('../package.json').version;
var releasePackageName = 'supla-cloud-v' + version + '.tar.gz';

project.printAsciiLogoAndVersion();

console.log('');
console.log("Preparing release package.");
console.log('');

function start() {
    clearVendorDirectory();
}

function clearVendorDirectory() {
    var spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
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
    var spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
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
    var spinner = ora({text: 'Copying application files.', color: 'yellow'}).start();
    var calls = [];
    [
        'app/',
        'bin/',
        'src/SuplaBundle',
        'vendor/',
        'web/',
    ].forEach(function (filename) {
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
        'var/logs',
        'var/sessions',
    ].forEach(function (dirname) {
        fs.mkdirsSync('release/' + dirname);
    });
}

function copySingleRequiredFiles() {
    fs.copySync('src/.htaccess', 'release/src/.htaccess');
    fs.copySync('README.md', 'release/README.md');
    fs.copySync('var/SymfonyRequirements.php', 'release/var/SymfonyRequirements.php');
}

function clearLocalConfigFiles() {
    del.sync([
        'release/**/.gitignore',
        'release/app/config/config_dev.yml',
        'release/app/config/config_test.yml',
        'release/app/config/parameters.yml',
        'release/app/config/parameters.yml.travis',
        'release/app/config/routing_dev.yml',
        'release/src/DeveloperBundle',
        'release/src/*/Tests',
        'release/web/app_dev.php',
        'release/web/assets/**/*.map',
    ]);
}

function createZipArchive() {
    var spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
    exec('tar -czf ' + releasePackageName + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
        if (err) {
            spinner.fail();
            console.log(err);
        } else {
            spinner.succeed('Release archive created.');
            console.log('');
            console.log("Package: " + chalk.green(releasePackageName));
        }
    });
}

start();

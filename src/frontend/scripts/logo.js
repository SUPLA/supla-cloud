var chalk = require('chalk');
var fs = require('fs');
var path = require('path');

var ASCII_LOGO_WIDTH = 52;
var LOGO = fs.readFileSync(path.join(__dirname, 'logo.txt'));

var printAsciiLogoAndVersion = function (version) {
    var versionWithV = 'v' + version;
    var versionLine = Array(ASCII_LOGO_WIDTH - versionWithV.length).join(' ') + versionWithV;
    console.log(chalk.green(LOGO));
    console.log(chalk.cyan(versionLine));
};

module.exports = {
    printAsciiLogoAndVersion: printAsciiLogoAndVersion
};

var runningAsScript = require.main === module;
if (runningAsScript) {
    printAsciiLogoAndVersion();
}

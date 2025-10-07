import * as fs from "node:fs";
import * as path from "node:path";
import chalk from "chalk";

const ASCII_LOGO_WIDTH = 52;
const LOGO = fs.readFileSync(path.join(__dirname, 'logo.txt'));

export const printAsciiLogoAndVersion = function (version) {
  const versionWithV = 'v' + version;
  const versionLine = Array(ASCII_LOGO_WIDTH - versionWithV.length).join(' ') + versionWithV;
  console.log(chalk.green(LOGO));
  console.log(chalk.cyan(versionLine));
};

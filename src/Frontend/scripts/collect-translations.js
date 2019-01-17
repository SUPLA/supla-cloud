const findInFiles = require('find-in-files');
const yaml = require('js-yaml');
const fs = require('fs');

const regexes = [
    "\\$t\\([\"'](.+?)[\"'].*?\\)", // $t('...')
    "[\"'](.+?)[\"'].*//\\s*i18n", // '...' // i18n
    "-i18n=\"(.+?)\"", // any-i18n="..."
    "i18n:(\\[.+?\\])", // // i18n:['...']
];

const locations = [
    'src',
    '../SuplaBundle',
    '../../app/Resources'
];

async function readFiles() {
    const texts = [];
    for (const regex of regexes) {
        for (const location of locations) {
            await findInFiles.find(regex, location, '.(vue|js|php|twig|html)$')
                .then(function (results) {
                    for (let result in results) {
                        results[result].matches.forEach(match => {
                            const text = match.match(regex);
                            if (regex == regexes[3]) {
                                const translations = eval(text[1]);
                                translations.forEach(t => texts.push(t));
                            } else {
                                texts.push(text[1]);
                            }
                        });
                    }
                });
        }
    }
    return texts
        .filter(elem => elem.indexOf('$t(') === -1)
        .filter((elem, pos, arr) => arr.indexOf(elem) == pos)
        .map(t => t.replace("\\'", "'"));
}

readFiles()
    .then(textsInSources => {
        const translationsDirectory = '../SuplaBundle/Resources/translations/';
        const yamlDumpConfig = {
            styles: {'!!null': 'canonical'},
            sortKeys: true,
            lineWidth: 1000
        };
        fs.readdirSync(translationsDirectory).forEach(file => {
            let translationFilePath = `${translationsDirectory}/${file}`;
            const existingMessages = yaml.safeLoad(fs.readFileSync(translationFilePath, 'utf8'));

            const matched = {};
            let missing = {};

            for (let text of textsInSources) {
                if (existingMessages[text]) {
                    matched[text] = existingMessages[text];
                } else if (file != 'messages.en.yml') {
                    missing[text] = null;
                }
                delete existingMessages[text];
            }

            const matchedYml = yaml.safeDump(matched, yamlDumpConfig);
            const missingYml = yaml.safeDump(missing, yamlDumpConfig);
            const extraYml = yaml.safeDump(existingMessages, yamlDumpConfig);

            const matchedCount = Object.keys(matched).length;
            const extraCount = Object.keys(existingMessages).length;
            const missingCount = Object.keys(missing).length;

            console.log(file);
            console.log(`Correct translations: ${matchedCount}`);
            console.log(`Extra translations: ${extraCount}`);
            console.log(`Missing translations: ${missingCount}`);
            console.log('');

            fs.writeFileSync(translationFilePath, '# Do not add new translation keys manually. Run npm run collect-translations in order to update this file.\n#<editor-fold desc="Complete translations" defaultstate="collapsed">\n');
            fs.appendFileSync(translationFilePath, matchedYml);
            fs.appendFileSync(translationFilePath, '\n#</editor-fold>\n');
            if (extraCount) {
                fs.appendFileSync(translationFilePath, '#<editor-fold desc="Extra translations">\n# Translations below have not been found in sources. You might want to delete them.\n');
                fs.appendFileSync(translationFilePath, extraYml);
                fs.appendFileSync(translationFilePath, '\n#</editor-fold>\n');
            }
            if (missingCount) {
                fs.appendFileSync(translationFilePath, '#<editor-fold desc="Missing translations">\n# Translations below are missing. You want to translate them.\n');
                fs.appendFileSync(translationFilePath, missingYml);
                fs.appendFileSync(translationFilePath, '\n#</editor-fold>\n');
            }
        });
    });


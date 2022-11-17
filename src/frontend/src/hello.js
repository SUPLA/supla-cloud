function consoleHello() {
    const HOMEPAGE = String.fromCodePoint(0x1F49A);
    const FORUM = String.fromCodePoint(0x1F4AC);
    const ISSUES = String.fromCodePoint(0x1f91d);
    // eslint-disable-next-line no-console
    console.log(`%cWelcome to SUPLA-Cloud!%c
Have you noticed a bug? Are you looking for an improvement? We like your curiosity!
Open an issue or contribute with a pull request to help make SUPLA-Cloud more enjoyable!
We are also always happy to hear your thoughts or doubts on the forum. See you!
${HOMEPAGE} https://supla.org
${FORUM} https://forum.supla.org
${ISSUES} https://github.com/SUPLA/supla-cloud/issues
`, 'font-size: 2em; padding-top: 0.5em', 'padding: 0.5em 0');
}

if (process.env.NODE_ENV === 'production') {
    consoleHello();
}

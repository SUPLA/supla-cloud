describe('KPOP', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '06_kpop_module.sql']);
    });

    it('waits for config initialization after function change', () => {
        cy.login();
        cy.visit('/channels/1/reactions');
        cy.contains('Zmień funkcję').click();
        cy.contains('Brak (kanał wyłączony)').click();
        cy.get('a.confirm').click();
        cy.contains('Wybierz funkcję').click();
        cy.contains('a', 'Ogólny kanał pomiarowy').click();
        cy.get('a.confirm').click();
        cy.contains('Konfiguracja jeszcze nie jest dostępna');
        cy.task('sql', `UPDATE supla_dev_channel
                        SET user_config='{"valueDivider":12,"valueMultiplier":34,"valueAdded":56,"valuePrecision":2,"unitBeforeValue":"ABCD","unitAfterValue":"EFGH","noSpaceAfterValue":false,"keepHistory":true,"chartType":"CANDLE"}'
                        WHERE id = 1`);
        cy.contains('Ustawienia pomiaru', {timeout: 10000}).click();
        cy.contains('ABCD 283.39 EFGH');
    });
})

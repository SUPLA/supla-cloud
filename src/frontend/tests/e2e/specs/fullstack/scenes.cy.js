describe('Scenes', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '02_sonoff.sql']);
    });

    it('contains no scenes at start', () => {
        cy.login();
        cy.visit('/scenes');
        cy.contains('Pusto!');
    });

    it('can create a scene', () => {
        cy.login();
        cy.visit('/scenes');
        cy.contains('Utwórz nową scenę').click();
        cy.get('[type=text]').type('Testowa scena');
        cy.contains('Kanały').click();
        cy.get('.channel-dropdown .ts-control').click();
        cy.contains('ID1 Włącznik światła').click({force: true});
        cy.contains('Wyłącz').click();
        cy.contains('Dodaj opóźnienie').click();
        cy.contains('Kanały').click();
        cy.get('.channel-dropdown .ts-control').click();
        cy.contains('ID1 Włącznik światła').click();
        cy.contains('Wyślij powiadomienie').click();
        cy.contains('.form-group', 'Treść').find('.form-control').type('Testowe powiadomienie');
        cy.get('.aid-dropdown .ts-control').click();
        cy.contains('AID #1').click();
        cy.contains('Dodaj opóźnienie').click();
        cy.contains('Zapisz zmiany').click();
        cy.contains('div.square-link', 'Testowa scena');
        cy.contains('div.square-link', '10 sek');
        cy.contains('div.square-link dt', '4'); // the number of operations
    });

    it('contains saved scene', () => {
        cy.login();
        cy.visit('/scenes');
        cy.contains('Pusto!').should('not.exist');
        cy.contains('div.square-link', 'Testowa scena');
    });
})

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
        cy.contains('Kanały').click();
        cy.contains('wybierz kanał').click();
        cy.contains('ID1 Włącznik światła').click({force: true});
        cy.contains('Wyłącz').click();
        cy.contains('Dodaj opóźnienie').click();
        cy.contains('Kanały').click();
        cy.contains('wybierz kanał').click({force: true});
        cy.contains('ID1 Włącznik światła').click();
        cy.get('[type=text]').type('Testowa scena');
        cy.contains('Wyślij powiadomienie').click();
        cy.get('[name=notification-body]').type('Testowe powiadomienie');
        cy.contains('wybierz identyfikatory dostępu').click();
        cy.get('a.dropdown-item').click();
        cy.contains('Zapisz zmiany').click();
        cy.contains('div.square-link', 'Testowa scena');
    });

    it('contains saved scene', () => {
        cy.login();
        cy.visit('/scenes');
        cy.contains('Pusto!').should('not.exist');
        cy.contains('div.square-link', 'Testowa scena');
    });
})

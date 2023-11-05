describe('HVAC', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '04_hvac_module.sql']);
    });

    it('can edit program settings', () => {
        cy.login();
        cy.visit('/channels/4');
        cy.contains('Edytuj ustawienia programu').click();
        cy.get('.thermostat-program-button-4 input').first().type('20');
        cy.contains('OK').click();
        cy.contains('Zapisz zmiany').click();
        cy.contains('Harmonogramy (0)').click();
        cy.contains('Tydzień').click();
        cy.contains('.thermostat-program-button-4', '20°C')
    });

    it('can edit week schedule', () => {
        cy.login();
        cy.visit('/channels/4');
        for (let i = 0; i < 5; i++) {
            cy.get('.thermostat-program-button-' + (i % 5)).click();
            cy.get('.time-slot').eq(i).click();
        }
        cy.contains('Zapisz zmiany').click();
        cy.contains('Harmonogramy (0)').click();
        cy.contains('Tydzień').click();
        for (let i = 0; i < 5; i++) {
            cy.get('.time-slot').eq(i).should('have.class', 'time-slot-mode-' + i);
        }
    });
})

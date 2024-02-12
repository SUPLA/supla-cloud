describe('Action Triggers', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '02_sonoff.sql', '03_uni_module.sql']);
    });

    it('can define an action trigger', () => {
        cy.login();
        cy.visit('/channels/1/action-triggers');
        cy.contains('Przełączenie 4x').click();
        cy.contains('Kanały').click();
        cy.get('#collapseTOGGLE_X4 .panel-body .panel-body .channel-dropdown .ts-control').click();
        cy.contains('.subject-dropdown-option', 'ID4').click();
        cy.contains('button', 'Zapisz zmiany').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 4x');
    });

    it('can define an action trigger with COPY action', () => {
        cy.login();
        cy.visit('/channels/1/action-triggers');
        cy.contains('Przełączenie 3x').click();
        cy.contains('Kanały').click();
        cy.get('#collapseTOGGLE_X3 .panel-body .panel-body .channel-dropdown .ts-control').click();
        cy.contains('.subject-dropdown-option', 'ID4').click();
        cy.contains('.channel-params-action-trigger-selector a', 'Skopiuj stan').click();
        cy.get('#collapseTOGGLE_X3 .panel-body .panel-body .panel-body .channel-dropdown .ts-control').click();
        cy.contains('#collapseTOGGLE_X3 .channel-action-chooser .subject-dropdown-option', 'Et velit dolor').click();
        cy.contains('button', 'Zapisz zmiany').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x');
    });

    it('can change the action and revert', () => {
        cy.login();
        cy.visit('/channels/1/action-triggers');
        cy.contains('#collapseTOGGLE_X3 .panel-heading', 'Włącz').click();
        cy.contains('a', 'Anuluj zmiany').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x');
        cy.contains('.panel-success .panel-heading', 'Skopiuj stan z innego');
        cy.contains('#collapseTOGGLE_X3 .ts-control .item', 'Et velit dolor');
    });

    it('can clear the action and revert', () => {
        cy.login();
        cy.visit('/channels/1/action-triggers');
        cy.contains('#collapseTOGGLE_X3 .panel-heading', 'Włącz').click();
        cy.contains('#collapseTOGGLE_X3 button', 'Wyczyść').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x').should('not.exist');
        cy.contains('a', 'Anuluj zmiany').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x');
        cy.contains('.panel-success .panel-heading', 'Skopiuj stan z innego');
        cy.contains('#collapseTOGGLE_X3 .ts-control .item', 'Et velit dolor');
    });

    it('can clear the action', () => {
        cy.login();
        cy.visit('/channels/1/action-triggers');
        cy.contains('#collapseTOGGLE_X3 .panel-heading', 'Włącz').click();
        cy.contains('#collapseTOGGLE_X3 button', 'Wyczyść').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x').should('not.exist');
        cy.contains('button', 'Zapisz zmiany').click();
        cy.contains('.panel-success .panel-heading', 'Przełączenie 3x').should('not.exist');
    });
})

describe('Action Executor', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '02_sonoff.sql', '03_uni_module.sql']);
    });

    it('can execute simple action', () => {
        cy.login();
        cy.visit('/channels/1');
        cy.contains('.channel-action-chooser a', 'Włącz').click();
        cy.contains('.channel-action-chooser .panel-success', 'Włącz');
    });

    it('can execute action with params', () => {
        cy.login();
        cy.visit('/channels/1');
        cy.contains('.channel-action-chooser a', 'Skopiuj stan').click();
        cy.get('.channel-action-chooser .ts-control').click();
        cy.contains('.channel-action-chooser .subject-dropdown-option', 'ID4').click();
        cy.contains('.channel-action-chooser .btn-execute', 'Wykonaj').click();
        cy.contains('.channel-action-chooser .btn-green', 'wykonano');
    });
})

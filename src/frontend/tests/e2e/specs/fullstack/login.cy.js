describe('Login tests', () => {
    before(function () {
        cy.task('seed:database', '01_user.sql');
    });

    it('Visits the app root url', () => {
        cy.visit('/')
        cy.contains('button', 'Sign In')
    });

    it('Displays bad password error on login error', () => {
        cy.visit('/')
        cy.get('input[type=email]').type(`user${Math.floor(Math.random() * 100)}@supla.org`);
        cy.get('input[type=password]').type('badpassword');
        cy.get('button[type=submit]').click();
        cy.contains('a.error', 'Forgot your password?');
    });

    it('Performs successful authentication', () => {
        cy.login();
        cy.visit('/')
        cy.get('.square-link').should('have.length.at.least', 4)
        cy.contains('a', 'Temperatura');
    });

    it('Enables IO devices registration', () => {
        cy.login();
        cy.visit('/');
        cy.contains('button', 'Rejestracja urządzeń: NIEAKTYWNA').click();
        cy.contains('button', 'Rejestracja urządzeń: AKTYWNA');
    });

    it('Enables smartphones registration', () => {
        cy.login();
        cy.visit('/smartphones');
        cy.contains('button', 'Rejestracja klientów: NIEAKTYWNA').click();
        cy.contains('button', 'Rejestracja klientów: AKTYWNA');
    });
})

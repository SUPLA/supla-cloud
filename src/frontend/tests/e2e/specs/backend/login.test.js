describe('Login tests', () => {
    it('Visits the app root url', () => {
        cy.visit('/')
        cy.contains('button', 'Sign In')
    });

    it('Displays bad password error on login error', () => {
        cy.visit('/')
        cy.get('input[type=email]').type('baduser@supla.org');
        cy.get('input[type=password]').type('badpassword');
        cy.get('button[type=submit]').click();
        cy.contains('a.error', 'Forgot your password?');
    });

    it('Performs successful authentication', () => {
        cy.visit('/')
        cy.get('input[type=email]').type('user@supla.org');
        cy.get('input[type=password]').type('pass');
        cy.get('button[type=submit]').click();
        cy.contains('.active', 'My SUPLA');
        cy.get('.square-link').should('have.length', 4)
        cy.contains('a', 'MEGA DEVICE');
    });
})

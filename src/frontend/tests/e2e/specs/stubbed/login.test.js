describe('Login tests', () => {
    beforeEach(function () {
        cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'})
    });

    it('Visits the app root url', () => {
        cy.visit('/')
        cy.contains('button', 'Sign In')
    });

    it('Displays bad password error on login error', () => {
        cy.intercept('POST', 'api/webapp-auth', (request) => {
            request.alias = 'invalidpass';
            expect(request.body).to.haveOwnProperty('username');
            expect(request.body).to.haveOwnProperty('password');
            expect(request.body.username).to.equal('baduser@supla.org');
            expect(request.body.password).to.equal('badpassword');
            request.reply({
                statusCode: 401,
                body: {"error": "invalid_grant", "error_description": "Invalid username and password combination"},
            });
        });
        cy.visit('/')
        cy.get('input[type=email]').type('baduser@supla.org');
        cy.get('input[type=password]').type('badpassword');
        cy.get('button[type=submit]').click();
        cy.contains('a.error', 'Forgot your password?');
    });

    it('Displays locked error if account is locked', () => {
        cy.intercept('POST', 'api/webapp-auth', {
            statusCode: 429,
            body: {"error": "locked", "error_description": "Your account has been blocked for a while."},
        });
        cy.visit('/')
        cy.get('input[type=email]').type('baduser@supla.org');
        cy.get('input[type=password]').type('badpassword');
        cy.get('button[type=submit]').click();
        cy.get('.error.locked').should('be.visible');
    });

    it('Performs successful authentication', () => {
        cy.intercept('POST', 'api/webapp-auth', {fixture: 'access-token'});
        cy.intercept('GET', 'api/users/current', {fixture: 'current-user.json'});
        cy.intercept('GET', 'api/iodevices?*', {headers: {'X-Total-Count': '4'}, fixture: 'iodevices.json'});
        cy.visit('/')
        cy.get('input[type=email]').type('user@supla.org');
        cy.get('input[type=password]').type('pass');
        cy.get('button[type=submit]').click();
        cy.contains('.active', 'My SUPLA');
        cy.get('.square-link').should('have.length', 4)
        cy.contains('a', 'MEGA DEVICE');
    });
})

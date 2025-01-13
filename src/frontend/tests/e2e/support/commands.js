Cypress.Commands.add('loginStub', (username = 'user@supla.org', password = 'pass') => {
    cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'});
    cy.intercept('POST', 'api/webapp-auth', {fixture: 'access-token'});
    cy.intercept('api/users/current', {fixture: 'current-user.json'});
    cy.intercept('GET', 'api/iodevices?*', {headers: {'X-Total-Count': '4'}, fixture: 'iodevices.json'});
    cy.intercept('GET', 'api/channels?*', {headers: {'X-Total-Count': '4'}, fixture: 'iodevices.json'});
    cy.intercept('GET', 'api/locations', {headers: {'X-Total-Count': '4'}, fixture: 'iodevices.json'});
    cy.login(username, password);
});

Cypress.Commands.add('login', (username = 'user@supla.org', password = 'pass') => {
    cy.session(username, () => {
        cy.visit('/');
        cy.get('input[type=email]').type(username);
        cy.get('input[type=password]').type(password);
        cy.get('button[type=submit]').click();
        cy.contains('.active', 'SUPLA');
    }, {
        validate() {
            const token = localStorage.getItem('supla-user-token');
            expect(token).not.to.be.undefined;
            // if not stubbed token
            if (token !== 'Yjg5OGU2NzM1MTk3MDRiZmUyNDAxZTQxZWE5YTU1OTM5MmNiNGY3ZjMwOWM5ZjkwNjI5NDQ3NjY0YTdhZTgzMw.aHR0cDovL3N1cGxhLmxvY2Fs') {
                cy.request({url: '/api/users/current', auth: {bearer: token}}).its('status').should('eq', 200);
            }
        },
    });
});

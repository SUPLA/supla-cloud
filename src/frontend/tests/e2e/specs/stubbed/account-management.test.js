describe('Account management tests', () => {
    beforeEach(function () {
        cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'})
    });

    describe('Account removal', () => {
        it('successfully deletes an account', () => {
            cy.intercept('GET', 'api/confirm-deletion/THE_TOKEN', {body: {exists: true}});
            cy.intercept('PATCH', 'api/confirm-deletion', (req) => {
                expect(req.body.token).to.eq('THE_TOKEN');
                expect(req.body.username).to.eq('user@supla.org');
                expect(req.body.password).to.eq('somepassword');
                req.reply({});
            });
            cy.visit('/');
            cy.visit('confirm-deletion/THE_TOKEN?lang=pl');
            cy.contains('button', 'Usuń moje konto');
            cy.get('input[type=email]').type('user@supla.org');
            cy.get('input[type=password]').type('somepassword');
            cy.get('input[type=checkbox]').click();
            cy.get('button.btn-danger').click();
            cy.contains('div', 'Konto zostało usunięte');
        });

        it('visits deletion page with invalid token', () => {
            cy.intercept('GET', 'api/confirm-deletion/THE_TOKEN', {body: {exists: false}, statusCode: 400});
            cy.intercept('PATCH', 'api/confirm-deletion', (req) => {
                expect(req.body.token).to.eq('THE_TOKEN');
                expect(req.body.username).to.eq('user@supla.org');
                expect(req.body.password).to.eq('somepassword');
                req.reply({});
            });
            cy.visit('/');
            cy.visit('confirm-deletion/THE_TOKEN?lang=pl');
            cy.contains('div', 'Token nie istnieje');
        });

        it('must enter correct username and password', () => {
            cy.intercept('GET', 'api/confirm-deletion/THE_TOKEN', {body: {exists: true}});
            cy.intercept('PATCH', 'api/confirm-deletion', {body: {error: true}, statusCode: 400});
            cy.visit('/');
            cy.visit('confirm-deletion/THE_TOKEN?lang=pl');
            cy.get('input[type=email]').type('user@supla.org');
            cy.get('input[type=password]').type('somepassword');
            cy.get('input[type=checkbox]').click();
            cy.get('button.btn-danger').click();
            cy.contains('div', 'Niepoprawna nazwa użytkownika lub hasło');
        });
    });
})

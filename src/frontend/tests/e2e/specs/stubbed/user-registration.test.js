describe('User registration', () => {
    beforeEach(function () {
        cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'})
    });

    it('successfully deletes an account', () => {
        cy.intercept('GET', 'api/account-deletion/THE_TOKEN', {body: {exists: true}});
        cy.intercept('PATCH', 'api/account-deletion', (req) => {
            expect(req.body.token).to.eq('THE_TOKEN');
            expect(req.body.username).to.eq('user@supla.org');
            expect(req.body.password).to.eq('somepassword');
            req.reply({});
        });
        cy.visit('register');
        cy.contains('button', 'Usuń moje konto');
        cy.get('input[type=email]').type('user@supla.org');
        cy.get('input[type=password]').type('somepassword');
        cy.get('input[type=checkbox]').click();
        cy.get('button.btn-danger').click();
        cy.contains('div', 'Konto zostało usunięte');
    });
})

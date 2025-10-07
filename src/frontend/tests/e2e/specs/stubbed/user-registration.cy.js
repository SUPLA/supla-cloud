describe('User registration', () => {
    beforeEach(function () {
        cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'})
    });

    it('displays errors for fields', () => {
        cy.visit('register');
        cy.get('.has-error').should('not.exist');
        cy.contains('button', 'Create an account').click();
        cy.get('.has-error').should('exist');
        cy.contains('.help-block', 'Please enter a valid email');
    });

    it('displays errors after blur', () => {
        cy.visit('register');
        cy.get('[type=password]').first().type('password');
        cy.get('.has-error').should(elements => expect(elements).to.have.length(1))
    });

    const registerUserSuplaOrg = () => {
        cy.get('#email')
            .type('user@supla.org')
            .tab()
            .type('strongpassword')
            .tab()
            .type('strongpassword')
            .tab()
            .check()
            .tab()
            .click();
    };

    it('registers successfully', () => {
        cy.visit('register');
        cy.intercept('POST', 'api/register-account', {
            statusCode: 201,
            body: {
                "id": 5,
                "shortUniqueId": "34d7147df2a63afa287b5fe56aa3083d",
                "email": "user@supla.org",
                "enabled": false,
                "timezone": "Europe/Warsaw"
            },
        })
        registerUserSuplaOrg();
        cy.contains('h1', 'Check your email inbox');
        cy.contains('p', 'user@supla.org');
    });

    it('registers unsuccessfully (email exists, account disabled)', () => {
        cy.visit('register');
        cy.intercept('POST', 'api/register-account', {
            statusCode: 409,
            body: {"status": 409, "message": "Email already exists", "accountEnabled": false},
        })
        registerUserSuplaOrg();
        cy.contains('.alert', 'Email already exists');
        cy.contains('.alert', 'check your SPAM');
    });

    it('registers unsuccessfully (email exists, account enabled)', () => {
        cy.visit('register');
        cy.intercept('POST', 'api/register-account', {
            statusCode: 409,
            body: {"status": 409, "message": "Email already exists", "accountEnabled": true},
        })
        registerUserSuplaOrg();
        cy.contains('.alert', 'Email already exists');
        cy.contains('.alert', 'check your SPAM').should('not.exist');
    });

    it('registers unsuccessfully (invalid captcha)', () => {
        cy.visit('register');
        cy.intercept('POST', 'api/register-account', {
            statusCode: 400,
            body: {"status": 400, "message": "Captcha token is not valid."},
        })
        registerUserSuplaOrg();
        cy.contains('.alert', 'Captcha token is not valid.');
    });
})

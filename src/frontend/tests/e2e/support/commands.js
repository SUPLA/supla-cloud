// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

Cypress.Commands.add('suplaLogin', (stubBackend = true) => {
    if (stubBackend) {
        cy.intercept('GET', 'api/server-info', {fixture: 'server-info.json'});
        cy.intercept('POST', 'api/webapp-auth', {fixture: 'access-token'});
        cy.intercept('api/users/current', {fixture: 'current-user.json'});
        cy.intercept('GET', 'api/iodevices?*', {headers: {'X-Total-Count': '4'}, fixture: 'iodevices.json'});
    }
    cy.visit('/');
    cy.get('input[type=email]').type('user@supla.org');
    cy.get('input[type=password]').type('pass');
    cy.get('button[type=submit]').click();
    cy.contains('.active', 'My SUPLA');
})

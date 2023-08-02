describe('Channel details page', () => {
    beforeEach(function () {
        cy.loginStub();
        cy.intercept('GET', 'api/channels/2?include=iodevice*', {fixture: 'channel-2.json'});
        cy.intercept('GET', 'api/channels/2/direct-links*', {body: []});
        cy.intercept('GET', 'api/iodevices/1?include=connected', {body: {id: 1, connected: true}});
        cy.visit('/channels/2');
    });

    it('Displays normal temperature', () => {
        cy.intercept('GET', 'api/channels/2?include=state', {body: {state: {temperature: 12.34}}});
        cy.contains('dt', '12.34°C');
    });

    it('Updates temperature', () => {
        let temperature = 13.34;
        cy.intercept('GET', 'api/channels/2?include=state', (req) => {
            temperature += 1;
            req.reply({body: {state: {temperature}}});
        }).as('stateRequest');
        cy.wait('@stateRequest');
        cy.contains('dt', '14.34°C');
        cy.wait('@stateRequest', {timeout: 10000});
        cy.contains('dt', '15.34°C');
    });

    it('Does not display -273 temperature', () => {
        cy.intercept('GET', 'api/channels/2?include=state', {body: {state: {temperature: -273}}});
        cy.contains('dt', '?°C');
    });
})

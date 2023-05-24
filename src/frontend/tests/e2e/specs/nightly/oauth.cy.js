describe('OAuth tests', () => {
    it('Opens OAuth authorize login form', () => {
        cy.visit('/oauth/v2/auth?client_id=2_2v9sx1qc6wqocows4ocoo80s4gggg4wcgogk40k4ocosk4o0ck&redirect_uri=http://suplascripts.local/authorize&state=noremember&response_type=code&scope=account_r iodevices_r channels_ea channels_r offline_access state_webhook');
        cy.contains('button', 'Sign In').should('be.visible');
    });
})

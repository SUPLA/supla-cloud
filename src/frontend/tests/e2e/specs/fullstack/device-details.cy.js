describe('Device details', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '02_sonoff.sql']);
    });

    describe('config', () => {
        it('can update config', () => {
            cy.login();
            cy.visit('/devices/1');
            cy.contains('Ustawienia').click();
            cy.get('#statusLed').should('have.value', 'ON_WHEN_CONNECTED')
            cy.get('#statusLed').select('ALWAYS_OFF');
            cy.contains('Zapisz zmiany').click();
            cy.contains('Kanały').click();
            cy.contains('Ustawienia').click();
            cy.get('#statusLed').should('have.value', 'ALWAYS_OFF')
        });

        it('detects conflicts when config has been changed externally', () => {
            cy.login();
            cy.visit('/devices/1');
            cy.contains('Ustawienia').click();
            cy.get('#statusLed').should('have.value', 'ALWAYS_OFF')
            cy.task('sql', `UPDATE supla_iodevice
                            SET user_config='{"statusLed":"OFF_WHEN_CONNECTED"}'
                            WHERE id = 1`);
            cy.get('#statusLed').select('ON_WHEN_CONNECTED');
            cy.contains('Zapisz zmiany').click();
            cy.contains('Ustawienia nie zostały zapisane');
            cy.contains('Odśwież konfigurację').click();
            cy.get('#statusLed').should('have.value', 'OFF_WHEN_CONNECTED');
            cy.get('#statusLed').select('ON_WHEN_CONNECTED');
            cy.contains('Zapisz zmiany').click();
            cy.contains('Kanały').click();
            cy.contains('Ustawienia').click();
            cy.get('#statusLed').should('have.value', 'ON_WHEN_CONNECTED')
        });
    });
})

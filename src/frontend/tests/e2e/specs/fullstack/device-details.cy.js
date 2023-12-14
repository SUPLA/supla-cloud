describe('Device details', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '02_sonoff.sql', '05_locked_module.sql']);
    });

    describe('config', () => {
        it('can update config', () => {
            cy.login();
            cy.visit('/devices/1/channels');
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
            cy.visit('/devices/1/channels');
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

    describe('locked devices', () => {
        let unlockUrl;

        it('can request device unlock', () => {
            cy.login();
            cy.visit('/devices/4');
            cy.contains('The device is locked');
            cy.get('input[type=email]').type('installer@zamel.com');
            cy.contains('button', 'Request device unlock').click();
            cy.contains('You have requested');
            cy.task('getLastEmail', 'installer@zamel.com').then(email => {
                expect(email).not.to.be.null;
                expect(email.body).to.contain('/confirm-device-unlock');
                unlockUrl = email.body.match(/(\/confirm-device-unlock.+)/)[0];
            });
        });

        it('can unlock the device with the link', () => {
            cy.visit(unlockUrl);
            cy.contains('Sukces');
            cy.task('getLastEmail', 'user@supla.org').then(email => {
                expect(email).not.to.be.null;
                expect(email.headers.subject).to.contain('unlocked');
                expect(email.body).to.contain('/devices/4');
            });
        });
    });
})

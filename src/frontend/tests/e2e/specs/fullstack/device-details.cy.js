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

    // TODO does not work in CI - don't know why; probably email gateway does not work
    describe.skip('locked devices', () => {
        it('can request device unlock', () => {
            cy.login();
            cy.visit('/devices/4');
            cy.contains('Urządzenie zablokowane');
            cy.get('input[type=email]').type('installer@zamel.com');
            cy.contains('button', 'Zgłoś chęć odblokowania').click();
            cy.contains('Prośba została wysłana');
            cy.task('getLastEmail', 'installer@zamel.com').then(email => {
                expect(email).not.to.be.null;
                expect(email.body).to.contain('/confirm-device-unlock');
            });
        });

        it('can unlock the device with the link', () => {
            cy.visit('/confirm-device-unlock/4/abcdef?lang=pl');
            cy.contains('Sukces');
            cy.task('getLastEmail', 'user@supla.org').then(email => {
                expect(email).not.to.be.null;
                expect(email.headers.subject).to.contain('odblokowane');
                expect(email.body).to.contain('/devices/4');
            });
        });

        it('sees the unlocked device', () => {
            cy.login();
            cy.visit('/devices/4');
            cy.contains('Kanały');
        });
    });
})

describe('HVAC', () => {
    before(function () {
        cy.task('seed:database', ['01_user.sql', '04_hvac_module.sql']);
    });

    it('can edit program settings', () => {
        cy.login();
        cy.visit('/channels/4');
        cy.contains('Edytuj ustawienia programu').click();
        cy.get('.thermostat-program-button-4 input').first().type('20');
        cy.contains('OK').click();
        cy.contains('Zapisz zmiany').click();
        cy.contains('Sceny (0)').click();
        cy.contains('Tydzień').click();
        cy.contains('.thermostat-program-button-4', '20°C')
    });

    it('can edit week schedule', () => {
        cy.login();
        cy.visit('/channels/4/thermostat-programs');
        for (let i = 0; i < 5; i++) {
            cy.get('.thermostat-program-button-' + (i % 5)).click();
            cy.get('.time-slot').eq(i).click();
        }
        cy.contains('Zapisz zmiany').click();
        cy.contains('Sceny (0)').click();
        cy.contains('Tydzień').click();
        for (let i = 0; i < 5; i++) {
            cy.get('.time-slot').eq(i).should('have.class', 'time-slot-mode-' + i);
        }
    });

    it('waits for config initialization after function change', () => {
        cy.login();
        cy.visit('/channels/5/thermostat-programs');
        cy.contains('Zmień funkcję').click();
        cy.contains('Brak (kanał wyłączony)').click();
        cy.get('a.confirm').click();
        cy.contains('Czy na pewno chcesz zmienić funkcję');
        cy.get('a.confirm').click();
        cy.contains('Wybierz funkcję').click();
        cy.contains('Termostat automatyczny').click();
        cy.get('a.confirm').click();
        cy.contains('Konfiguracja jeszcze nie jest dostępna');
        cy.task('sql', `UPDATE supla_dev_channel
                        SET user_config='{"mainThermometerChannelNo":1,"auxThermometerChannelNo":null,"binarySensorChannelNo":5,"usedAlgorithm":"ON_OFF_SETPOINT_AT_MOST","weeklySchedule":{"programSettings":{"1":{"mode":"HEAT","setpointTemperatureHeat":2400,"setpointTemperatureCool":0},"2":{"mode":"HEAT","setpointTemperatureHeat":2100,"setpointTemperatureCool":0},"3":{"mode":"HEAT","setpointTemperatureHeat":1800,"setpointTemperatureCool":0},"4":{"mode":"NOT_SET","setpointTemperatureHeat":2200,"setpointTemperatureCool":0}},"quarters":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,0,0,0,0,0,0]}}'
                        WHERE id = 5`);
        cy.contains('Zachowanie', {timeout: 10000}).click();
        cy.contains('z nastawą górną');
    });
})

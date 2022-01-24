<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1 v-title>{{ $t('My SUPLA') }}</h1>
                    <div class="form-group">
                        <btn-filters id="mySuplaListType"
                            v-model="listType"
                            :filters="[{label: $t('I/O Devices'), value: 'devices'}, {label: $t('Channels'), value: 'channels'}, {label: $t('Dashboard'), value: 'dashboard'}]"
                        />
                    </div>
                </div>
                <devices-registration-button v-show="!$frontendConfig.maintenanceMode"
                    field="ioDevicesRegistrationEnabled"
                    caption-i18n="I/O devices registration"></devices-registration-button>
            </div>
        </div>
        <devices-list-page v-if="listType === 'devices'"/>
        <dashboard v-else-if="listType === 'dashboard'"/>
        <channel-list-page v-else/>
    </div>
</template>

<script>
    import DevicesRegistrationButton from "../devices/list/devices-registration-button";
    import DevicesListPage from "../devices/list/devices-list-page";
    import BtnFilters from "../common/btn-filters";
    import ChannelListPage from "../channels/channel-list-page";
    import Dashboard from "./dashboard";

    export default {
        components: {
            Dashboard,
            ChannelListPage,
            BtnFilters,
            DevicesListPage,
            DevicesRegistrationButton
        },
        data() {
            return {
                listType: 'devices'
            };
        }
    };
</script>

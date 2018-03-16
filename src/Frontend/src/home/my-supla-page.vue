<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1>Moja SUPLA</h1>
                    <btn-filters v-model="listType"
                        :filters="[{label: $t('Channels'), value: 'channels'}, {label: $t('I/O Devices'), value: 'devices'}]"></btn-filters>
                </div>
                <devices-registration-button field="ioDevicesRegistrationEnabled"
                    caption="Registration of new I/O devices"></devices-registration-button>
            </div>
        </div>
        <devices-list-page v-if="listType == 'devices'"></devices-list-page>
        <channel-list-page v-else></channel-list-page>
    </div>
</template>

<script>
    import DevicesRegistrationButton from "../devices/list/devices-registration-button";
    import DevicesListPage from "../devices/list/devices-list-page";
    import BtnFilters from "../common/btn-filters";
    import ChannelListPage from "../channels/channel-list-page";

    export default {
        components: {
            ChannelListPage,
            BtnFilters,
            DevicesListPage,
            DevicesRegistrationButton
        },
        data() {
            return {
                listType: 'channels'
            };
        }
    };
</script>

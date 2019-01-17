<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1 v-title>{{ $t('My SUPLA') }}</h1>
                    <div class="form-group">
                        <btn-filters id="mySuplaListType"
                            v-model="listType"
                            :filters="[{label: $t('I/O Devices'), value: 'devices'}, {label: $t('Channels'), value: 'channels'}]"></btn-filters>
                    </div>
                </div>
                <devices-registration-button field="ioDevicesRegistrationEnabled"
                    caption-i18n="Registration of new I/O devices"></devices-registration-button>
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
                listType: 'devices'
            };
        }
    };
</script>

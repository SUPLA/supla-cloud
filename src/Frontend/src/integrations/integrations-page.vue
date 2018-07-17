<template>
    <div>
        <div class="container">
            <h1 v-title>{{ $t('Integrations') }}</h1>
            <h4>{{$t('With integrations you can easily add features to your account, leveraging solutions provided by another developer or even better - by yourself.')}}</h4>
            <div class="form-group">
                <btn-filters v-model="tab"
                    :filters="[{label: $t('Authorized OAuth apps'), value: 'authorized-oauth-apps'},
                               {label: $t('My OAuth apps'), value: 'my-oauth-apps'},
                               {label: $t('Personal access tokens'), value: 'personal-tokens'}]"></btn-filters>
            </div>
            <router-view></router-view>
        </div>
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
                tab: undefined,
                listType: 'devices'
            };
        },
        mounted() {
            this.tab = this.$router.currentRoute.name;
        },
        watch: {
            tab(name) {
                if (name != this.$router.currentRoute.name) {
                    this.$router.push({name});
                }
            }
        }
    };
</script>

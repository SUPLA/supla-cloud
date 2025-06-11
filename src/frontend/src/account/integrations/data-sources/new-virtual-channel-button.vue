<template>
    <div>
        <a class="btn btn-green" @click="adding = true">
            <span>
                <i class="pe-7s-plus"></i>
                {{ $t('Add new data source') }}
            </span>
        </a>

        <modal-confirm
            v-if="adding"
            :header="$t('Add new data source')"
            :loading="loading"
            @confirm="addNewDataSource()"
            @cancel="adding = false">
            <!-- i18n: ['virtualChannelTypeName_OPEN_WEATHER', 'virtualChannelTypeInfo_OPEN_WEATHER'] -->
            <simple-dropdown :options="channelTypes" v-if="type" v-model="type">
                <template #option="{option}">
                    <div>
                        {{ $t('virtualChannelTypeName_' + option) }}
                        <div class="small">{{ $t('virtualChannelTypeInfo_' + option) }}</div>
                    </div>
                </template>
                <template #button="{value}">{{ $t('virtualChannelTypeName_' + value) }}</template>
            </simple-dropdown>
            <div class="small mt-2">
                {{ $t('virtualChannelTypeInfo_' + type) }}
            </div>
            <NewVirtualChannelParamsOpenWeather v-model="config" v-if="type === 'OPEN_WEATHER'"/>
            <div class="text-danger" v-if="errorMessage">{{ errorMessage }}</div>
        </modal-confirm>
    </div>
</template>

<script setup>
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";
    import {useSuplaApi} from "@/api/use-supla-api";
    import {ref} from "vue";
    import {channelsApi} from "@/api/channels-api";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useRouter} from "vue-router/composables";
    import NewVirtualChannelParamsOpenWeather from "@/account/integrations/data-sources/new-virtual-channel-params-open-weather.vue";
    import {useI18n} from "vue-i18n-bridge";

    const router = useRouter();
    const i18n = useI18n();

    const adding = ref(false);
    const loading = ref(false);
    const type = ref(null);
    const config = ref({});
    const errorMessage = ref('');

    const {data: channelTypes} = useSuplaApi(`enum/virtual-channel-types`, {
        afterFetch(ctx) {
            type.value = ctx.data[0];
        }
    }).json();

    async function addNewDataSource() {
        if (!config.value.ready) {
            errorMessage.value = i18n.t('Please fill all the fields');
            return;
        }
        errorMessage.value = '';
        loading.value = true;
        try {
            const {body: virtualChannel} = await channelsApi.createVirtualChannel(type.value, config.value);
            await useChannelsStore().refetchAll();
            await router.push({name: 'channel', params: {id: virtualChannel.id}});
        } catch (e) {
            errorMessage.value = i18n.t('Could not add the data source. Try again in a while.');
            loading.value = false;
        }
    }
</script>

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
            <!-- i18n: ['virtualChannelTypeName_OPEN_WEATHER'] -->
            <!-- i18n: ['virtualChannelTypeName_ENERGY_PRICE_FORECAST'] -->
            <div class="d-flex flex-wrap" v-if="channelTypes">
                <button
                    v-for="chType in channelTypes"
                    :key="chType"
                    type="button"
                    :class="['btn flex-grow-1 m-1', chType === type ? 'btn-green' : 'btn-default']"
                    @click="type = chType">
                    {{ $t('virtualChannelTypeName_' + chType) }}
                </button>
            </div>
            <NewVirtualChannelParamsOpenWeather v-model="config" v-if="type === 'OPEN_WEATHER'"/>
            <NewVirtualChannelParamsEnergyPriceForecast v-model="config" v-if="type === 'ENERGY_PRICE_FORECAST'"/>
            <div class="text-danger" v-if="errorMessage">{{ errorMessage }}</div>
        </modal-confirm>
    </div>
</template>

<script setup>
  import {useSuplaApi} from "@/api/use-supla-api";
  import {ref} from "vue";
  import {channelsApi} from "@/api/channels-api";
  import {useChannelsStore} from "@/stores/channels-store";
  import {useRouter} from "vue-router";
  import NewVirtualChannelParamsOpenWeather
    from "@/account/integrations/data-sources/new-virtual-channel-params-open-weather.vue";
  import {useI18n} from "vue-i18n";
  import NewVirtualChannelParamsEnergyPriceForecast
    from "@/account/integrations/data-sources/new-virtual-channel-params-energy-price-forecast.vue";
  import ModalConfirm from "@/common/modal-confirm.vue";

  const router = useRouter();
    const i18n = useI18n();

    const adding = ref(false);
    const loading = ref(false);
    const type = ref(null);
    const config = ref({});
    const errorMessage = ref('');

    const {data: channelTypes} = useSuplaApi(`enum/virtual-channel-types`).json();

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

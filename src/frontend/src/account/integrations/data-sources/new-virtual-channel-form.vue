<template>
    <square-link :class="`clearfix black not-transform`">
        <h3 class=" text-center">{{ $t('Add new data source') }}</h3>
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
        <div class="text-center mt-2 mb-1">
            <button class="btn btn-green" type="button" @click="addNewDataSource()" :disabled="adding">
                <button-loading-dots v-if="adding"></button-loading-dots>
                <span v-else>{{ $t('Add') }}</span>
            </button>
        </div>
    </square-link>
</template>

<script setup>
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";
    import {useSuplaApi} from "@/api/use-supla-api";
    import {ref} from "vue";
    import {channelsApi} from "@/api/channels-api";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useRouter} from "vue-router/composables";

    const router = useRouter();

    const type = ref(null);
    const {data: channelTypes} = useSuplaApi(`enum/virtual-channel-types`, {
        afterFetch(ctx) {
            type.value = ctx.data[0];
        }
    }).json();

    const adding = ref(false);

    async function addNewDataSource() {
        adding.value = true;
        const {body: virtualChannel} = await channelsApi.createVirtualChannel(type.value);
        await useChannelsStore().refetchAll();
        await router.push({name: 'channel', params: {id: virtualChannel.id}});
    }
</script>

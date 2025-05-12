<template>
    <square-link :class="`clearfix black not-transform`">
        <h3 class=" text-center">{{ $t('Add new data source') }}</h3>
        <!-- i18n: ['virtualChannelTypeName_OPEN_WEATHER', 'virtualChannelTypeInfo_OPEN_WEATHER'] -->
        <simple-dropdown :options="channelTypes" v-if="type" v-model="type">
            <template #option="{option}">
                <div>
                    {{ $t('virtualChannelTypeName_' + option.name) }}
                    <div class="small">{{ $t('virtualChannelTypeInfo_' + option.name) }}</div>
                </div>
            </template>
            <template #button="{value}">{{ $t('virtualChannelTypeName_' + value.name) }}</template>
        </simple-dropdown>
        <div class="small mt-2">
            {{ $t('virtualChannelTypeInfo_' + type.name) }}
        </div>
        <div class="text-center mt-2 mb-1">
            <button class="btn btn-green" type="button">
                {{ $t('Add') }}
            </button>
        </div>
    </square-link>
</template>

<script setup>
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";
    import {useSuplaApi} from "@/api/use-supla-api";
    import {ref} from "vue";

    const type = ref(null);
    const {data: channelTypes} = useSuplaApi(`enum/virtual-channel-types`, {
        afterFetch(ctx) {
            type.value = ctx.data[0];
        }
    }).json();
</script>

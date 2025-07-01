<template>
    <div>
        <div v-if="channel.config.virtualChannelConfig.type === 'ENERGY_PRICE_FORECAST'">
            <dl>
                <dd>{{ $t('Data source type') }}</dd>
                <dt>{{ $t('Energy prices') }}</dt>
            </dl>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_ENERGY_PRICE_FORECAST') }}</div>
            <dl>
                <dd>{{ $t('Attribute') }}</dd>
                <dt>{{ $t(`energyPriceForecast_field_${channel.config.virtualChannelConfig.energyField}`) }}</dt>
            </dl>
            <dl v-if="channel.config.virtualChannelConfig.energyField === 'pdgsz'" class="mt-2">
                <dd class="valign-top">{{ $t('Description') }}</dd>
                <dt>
                    <div class="pdgsz-legend d-flex" v-for="level in levels" :key="level.badge"
                        :style="{'border-color': level.color}">
                        <span class="mr-2"><span class="badge" :style="{background: level.color}">{{ level.badge }}</span></span>
                        <span>{{ $t(level.label) }}</span>
                    </div>
                </dt>
            </dl>
        </div>
    </div>
</template>

<script setup>
    defineProps({channel: Object});

    const levels = [
        {label: 'RECOMMENDED USE. Take advantage of excess energy.', color: '#226b11', badge: '0'}, // i18n
        {label: 'NORMAL USE. Use the energy as usual.', color: '#98c21d', badge: '1'}, // i18n
        {label: 'RECOMMENDED SAVING. Schedule energy-intensive household activities for other hours.', color: '#f2c433', badge: '2'}, // i18n
        {label: 'REQUIRED LIMITATION. Limit your electricity consumption to the essential minimum.', color: '#e42313', badge: '3'}, // i18n
    ];
</script>

<style scoped lang="scss">
    .pdgsz-legend {
        border-style: solid;
        border-width: 1px 0 0 0;
        margin-bottom: 1em;
        .badge {
            margin-top: -3px;
            border-radius: 0;
        }
    }
</style>

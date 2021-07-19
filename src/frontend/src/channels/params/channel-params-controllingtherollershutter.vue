<template>
    <div>
        <dl v-if="channel.config.timeSettingAvailable">
            <dd>{{ $t('Full opening time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.1"
                        min="0"
                        max="300"
                        class="form-control text-center"
                        v-model="channel.config.openingTimeS"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
            <dd>{{ $t('Full closing time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.1"
                        min="0"
                        max="300"
                        class="form-control text-center"
                        v-model="channel.config.closingTimeS"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Opening sensor') }}</dd>
            <dt>
                <channels-id-dropdown :params="channelsDropdownFilter"
                    v-model="channel.config.openingSensorChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown},
        props: ['channel', 'sensorFunction'],
        computed: {
            channelsDropdownFilter() {
                return 'function=' + (this.sensorFunction || 'OPENINGSENSOR_ROLLERSHUTTER');
            }
        }
    };
</script>

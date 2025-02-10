<template>
    <div>
        <dl>
            <dd>{{ $t('Fully opened sensor for channel') }}</dd>
            <dt>
                <channels-id-dropdown :params="'function=' + relatedChannelFunction"
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
        props: ['channel', 'relatedChannelFunction'],
    };
</script>

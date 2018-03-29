<template>
    <flipper :flipped="!!flipped">
        <channel-tile :model="channel"
            no-link="true"
            @click="flipped = true"
            slot="front"></channel-tile>
        <square-link :class="'clearfix pointer not-transform ' + (channel.enabled ? 'green' : 'grey')"
            slot="back">
            <a class="valign-center text-center"
                @click="flipped = false">
                <span class="channel-buttons">
                    <a class="btn btn-default"
                        @click.stop=""
                        :href="`/channels/${channel.id}` | withBaseUrl">
                        {{ $t('Go to channel details') }}
                    </a>
                    <a class="btn btn-default"
                        @click.stop=""
                        :href="`/devices/${channel.iodeviceId}` | withBaseUrl">
                        {{ $t('Go to I/O device details') }}
                    </a>
                    <button class="btn btn-danger btn-block"
                        type="button"
                        @click.stop="$emit('remove')"
                        :disabled="!removable">
                        {{ $t('Delete') }}
                    </button>
                </span>
            </a>
        </square-link>
    </flipper>
</template>

<script>
    import ChannelTile from "../channels/channel-tile";

    export default {
        props: ['channel', 'removable'],
        components: {ChannelTile},
        data() {
            return {
                flipped: false,
            };
        }
    };
</script>

<style>
    .channel-buttons .btn {
        margin-bottom: 5px;
        display: block;
    }
</style>

<template>
    <flipper :flipped="!!flipped">
        <channel-tile :model="channel"
            no-link
            @click="flipped = true"
            slot="front"></channel-tile>
        <square-link :class="'clearfix pointer not-transform ' + (channel.enabled ? 'green' : 'grey')"
            slot="back">
            <a class="valign-center text-center"
                @click="flipped = false">
                <span class="channel-buttons">
                    <router-link class="btn btn-default"
                        :to="{name: 'channel', params: {id: channel.id}}">
                        {{ $t('Go to channel details') }}
                    </router-link>
                    <router-link class="btn btn-default"
                        :to="{name: 'device', params: {id: channel.iodeviceId}}">
                        {{ $t('Go to I/O device details') }}
                    </router-link>
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

<script setup>
    import ChannelTile from "../channels/channel-tile";
    import {defineProps, ref} from "vue";

    defineProps({
        channel: Object,
        removable: {
            type: Boolean,
            default: true,
        },
    });

    const flipped = ref(false);
</script>

<style>
    .channel-buttons .btn {
        margin-bottom: 5px;
        display: block;
    }
</style>

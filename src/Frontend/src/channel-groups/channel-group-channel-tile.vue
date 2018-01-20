<template>
    <flipper :flipped="!!flipped">
        <square-link :class="'clearfix pointer ' + (channel.enabled ? 'green' : 'grey')"
            slot="front">
            <a @click="flipped = true">
                <function-icon :model="channel"
                    width="90"></function-icon>
                <h3>{{ $t(channel.function.caption) }}</h3>
                <dl>
                    <dd>{{ $t('Device') }}</dd>
                    <dt>{{ channel.iodevice.name }}</dt>
                    <dd>{{ $t('Type') }}</dd>
                    <dt>{{ $t(channel.type.caption) }}</dt>
                    <dd>{{ $t('Location') }}</dd>
                    <dt>ID{{channel.iodevice.location.id}} {{ channel.iodevice.location.caption }}</dt>
                </dl>
                <div v-if="channel.caption">
                    <div class="separator"></div>
                    {{ channel.caption }}
                </div>
                <div class="square-link-label">
                    <device-connection-status-label :device="channel.iodevice"></device-connection-status-label>
                </div>
            </a>
        </square-link>
        <square-link :class="'clearfix pointer not-transform ' + (channel.enabled ? 'green' : 'grey')"
            slot="back">
            <a class="valign-center text-center"
                @click="flipped = false">
                <span class="channel-buttons">
                    <a class="btn btn-default"
                        @click.stop=""
                        :href="`/iodev/${channel.iodeviceId}/${channel.id}/edit` | withBaseUrl">
                        {{ $t('Go to channel details') }}
                    </a>
                    <a class="btn btn-default"
                        @click.stop=""
                        :href="`/iodev/${channel.iodeviceId}/view` | withBaseUrl">
                        {{ $t('Go to I/O device details') }}
                    </a>
                    <a class="btn btn-danger"
                        @click.stop="$emit('remove')"
                        :disabled="!removable">
                        {{ $t('Remove') }}
                    </a>
                </span>
            </a>
        </square-link>
    </flipper>
</template>

<script>
    import FunctionIcon from "../channels/function-icon.vue";
    import DeviceConnectionStatusLabel from "../devices/list/device-connection-status-label.vue";

    export default {
        props: ['channel', 'removable'],
        components: {FunctionIcon, DeviceConnectionStatusLabel},
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

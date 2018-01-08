<template>
    <flipper :flipped="!!flipped">
        <square-link :class="'clearfix pointer ' + (channel.enabled ? 'green' : 'grey')"
            slot="front">
            <a @click="flipped = true">
                <function-icon :model="channel"
                    width="100"></function-icon>
                <h3>{{ $t(channel.function.caption) }}</h3>
                <dl>
                    <dd>{{ $t('Device') }}</dd>
                    <dt>{{ $t(channel.iodevice.name) }}</dt>
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
                        Go to channel details
                    </a>
                    <a class="btn btn-default"
                        @click.stop=""
                        :href="`/iodev/${channel.iodeviceId}/view` | withBaseUrl">
                        Go to I/O device details
                    </a>
                    <button class="btn btn-danger"
                        @click.stop="$emit('remove')"
                        :disabled="!removable">
                        Remove
                    </button>
                </span>
            </a>
        </square-link>
    </flipper>
</template>

<script>
    import SquareLink from "src/common/square-link.vue";
    import FunctionIcon from "./function-icon.vue";
    import Flipper from "../common/flipper.vue";
    import DeviceConnectionStatusLabel from "../devices/list/device-connection-status-label.vue";

    export default {
        props: ['channel', 'removable'],
        components: {FunctionIcon, SquareLink, Flipper, DeviceConnectionStatusLabel},
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
    }
</style>

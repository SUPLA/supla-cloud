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
                    <dt>{{ $t(channel.device) }}</dt>
                    <dd>{{ $t('Type') }}</dd>
                    <dt>{{ $t(channel.type.caption) }}</dt>
                    <!--<dd>{{ $t('Location') }}</dd>-->
                    <!--<dt>ID{{channel.location.id}} {{ channel.location.caption }}</dt>-->
                </dl>
                <div v-if="channel.caption">
                    <div class="separator"></div>
                    {{ channel.caption }}
                </div>
                <!--<div class="square-link-label">-->
                <!--<device-connection-status-label :device="channel.device"></device-connection-status-label>-->
                <!--</div>-->
            </a>
            <!--
        <a class="element-item nav-link {{ channel.function_id }} {% if channel.function_id == '0' %} none {% endif %}" href="{{ path('_iodev_channel_item_edit', {'devid': iodevice.id, 'id': channel.id}) }}">

                <img src={{ asset('assets/img/functions/') }}{{ channel.icon_filename }}>

                <h3>{{ channel.function }}</h3>
                <div class="detials">
                    {% trans %}Channel{% endtrans %} <strong> {{ channel.number }}</strong> ({{channel.io}})<br />
                    {% trans %}Type{% endtrans %} <strong>{{ channel.type }}</strong>
                </div>

                {% if channel.caption is not empty %}
                    <div class="caption">
                    {{ channel.caption }}
                    </div>
                {% endif %}
           </a>
           -->
        </square-link>
        <square-link :class="'clearfix pointer not-transform ' + (channel.enabled ? 'green' : 'grey')"
            slot="back">
            <a class="valign-center text-center"
                @click="flipped = false">
                <span>
                    <button class="btn btn-danger"
                        @click.stop="$emit('remove')">
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
        props: ['channel'],
        components: {FunctionIcon, SquareLink, Flipper, DeviceConnectionStatusLabel},
        data() {
            return {
                flipped: false,
            };
        }
    };
</script>

<template>
    <square-link :class="'clearfix pointer grey'"
        @click="$emit('click')">
        <a>
            <function-icon :model="model"
                width="90"></function-icon>
            <h3 class="no-margin-top">ID{{ model.id }} {{ $t(model.function.caption) }}</h3>
            <dl>
                <dd>{{ $t('Device') }}</dd>
                <dt>{{ model.iodevice.name }}</dt>
                <dd>{{ $t('Type') }}</dd>
                <dt>{{ $t(model.type.caption) }}</dt>
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{model.iodevice.location.id}} {{ model.iodevice.location.caption }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
            <div class="square-link-label">
                <device-connection-status-label :device="model.iodevice"></device-connection-status-label>
            </div>
        </a>
    </square-link>
</template>

<script>
    import FunctionIcon from "./function-icon";
    import DeviceConnectionStatusLabel from "../devices/list/device-connection-status-label.vue";

    export default {
        props: ['model'],
        components: {FunctionIcon, DeviceConnectionStatusLabel}
    };
</script>

<style scoped>

</style>

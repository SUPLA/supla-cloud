<template>
    <square-link :class="`clearfix pointer ${model.functionId == 0 ? 'yellow' : 'grey'}`"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <function-icon :model="model"
                width="90"></function-icon>
            <h3 class="no-margin-top">ID{{ model.id }} {{ $t(model.function.caption) }}</h3>
            <dl>
                <dd>{{ $t('Device') }}</dd>
                <dt>{{ model.iodevice.name }}</dt>
                <dd>{{ $t('Type') }}</dd>
                <dt>{{ $t(model.type.caption) }}</dt>
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{model.location.id}} {{ model.location.caption }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
            <div class="square-link-label"
                v-if="model.functionId != 0">
                <device-connection-status-label :device="model.iodevice"></device-connection-status-label>
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "./function-icon";
    import DeviceConnectionStatusLabel from "../devices/list/device-connection-status-label.vue";

    export default {
        props: ['model', 'noLink'],
        components: {FunctionIcon, DeviceConnectionStatusLabel},
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'channel', params: this.model};
            }
        }
    };
</script>

<style scoped>

</style>

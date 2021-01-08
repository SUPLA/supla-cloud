<template>
    <square-link :class="`clearfix pointer ${model.functionId == 0 ? 'yellow' : 'grey'}`"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <function-icon :model="model"
                width="90"></function-icon>
            <h3 class="no-margin-top">ID{{ model.id }} {{ $t(model.function.caption) }}</h3>
            <dl class="ellipsis"
                v-if="model.iodevice">
                <dd>{{ $t('Device') }}</dd>
                <dt>{{ model.iodevice.name }}</dt>
            </dl>
            <dl class="ellipsis">
                <dd>{{ $t('Type') }}</dd>
                <dt>{{ $t(model.type.caption) }}</dt>
            </dl>
            <dl class="ellipsis"
                v-if="model.location">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{model.location.id}} {{ model.location.caption }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
            <div class="square-link-label">
                <connection-status-label :model="model"></connection-status-label>
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "./function-icon";
    import ConnectionStatusLabel from "../devices/list/connection-status-label.vue";

    export default {
        props: ['model', 'noLink'],
        components: {FunctionIcon, ConnectionStatusLabel},
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'channel', params: {id: this.model.id}};
            }
        }
    };
</script>

<style scoped>

</style>

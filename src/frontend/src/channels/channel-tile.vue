<template>
    <square-link :class="`clearfix pointer ${model.functionId == 0 ? 'yellow' : 'grey'} ${model.functionId != 0 ? 'with-label' : ''}`"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <function-icon :model="model"
                    class="pull-right"
                    width="90"></function-icon>
                <h3 class="no-margin-top line-clamp line-clamp-4">{{ caption }}</h3>
            </div>
            <dl class="ellipsis"
                v-if="model.caption">
                <dd></dd>
                <dt>{{ $t(model.function.caption) }}</dt>
            </dl>
            <dl>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
            </dl>
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
            caption() {
                return this.model.caption || this.$t(this.model.function.caption);
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'channel', params: {id: this.model.id}};
            }
        }
    };
</script>

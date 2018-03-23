<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="{name: 'channelGroup', params: model}">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }} </strong></h2>
                <function-icon :model="model"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl>
                <dd>{{ $t('Channels no') }}</dd>
                <dt>{{ model.channelIds.length }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "../channels/function-icon.vue";

    export default {
        components: {FunctionIcon},
        props: ['model'],
    };
</script>

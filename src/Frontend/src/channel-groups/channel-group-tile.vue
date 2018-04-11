<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.hidden ? 'grey' : 'green')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }} </strong></h2>
                <function-icon :model="model"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl>
                <dd>{{ $t('No. of channels') }}</dd>
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
        props: ['model', 'noLink'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'channelGroup', params: {id: this.model.id}};
            }
        }
    };
</script>

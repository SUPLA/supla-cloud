<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }} </strong></h2>
                <function-icon :model="model"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl v-if="model.relationsCount">
                <dd>{{ $t('No of operations') }}</dd>
                <dt>{{ model.relationsCount.operations || 0 }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "../channels/function-icon";

    export default {
        components: {FunctionIcon},
        props: ['model', 'noLink'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'scene', params: {id: this.model.id}};
            }
        }
    };
</script>

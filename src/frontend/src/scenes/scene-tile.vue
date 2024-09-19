<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <function-icon :model="model"
                    class="pull-right"
                    width="60"></function-icon>
                <h3>{{ caption }}</h3>
            </div>
            <dl>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
                <dd>{{ $t('Estimated execution time') }}</dd>
                <dt>{{ prettyMilliseconds(model.estimatedExecutionTime) }}</dt>
            </dl>
            <dl v-if="model.relationsCount">
                <dd>{{ $t('No of operations') }}</dd>
                <dt>{{ model.relationsCount.operations || 0 }}</dt>
            </dl>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "../channels/function-icon";
    import {prettyMilliseconds} from "../common/filters";

    export default {
        components: {FunctionIcon},
        props: ['model', 'noLink'],
        computed: {
            caption() {
                return this.model.caption || this.$t('Scene') + ' ID' + this.model.id;
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'scene', params: {id: this.model.id}};
            }
        },
        methods: {
            prettyMilliseconds(ms) {
                return prettyMilliseconds(ms);
            }
        }
    };
</script>

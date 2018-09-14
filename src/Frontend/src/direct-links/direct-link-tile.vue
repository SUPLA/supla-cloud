<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.active ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }}</strong></h2>
                <function-icon :model="model.subject"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl>
                <dd>{{ $t('Last used') }}</dd>
                <dt v-if="model.lastUsed">{{ model.lastUsed | moment("LT L") }}</dt>
                <dt v-else>{{ $t('Never') }}</dt>
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
                return this.noLink ? {} : {name: 'directLink', params: {id: this.model.id}};
            }
        }
    };
</script>

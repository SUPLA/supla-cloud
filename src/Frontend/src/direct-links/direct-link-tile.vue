<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.active ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">{{ caption }}</h2>
                <function-icon :model="model.subject"
                    class="pull-right"
                    width="60"></function-icon>
            </div>
            <dl>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
                <dd>{{ $t('Last used') }}</dd>
                <dt v-if="model.lastUsed">{{ model.lastUsed | moment("LT L") }}</dt>
                <dt v-else>{{ $t('Never') }}</dt>
                <dd>{{ $t('Subject type') }}</dd>
                <dt>{{ $t('actionableSubjectType_' + model.subjectType) }}</dt>
            </dl>
        </router-link>
    </square-link>
</template>

<script>
    import FunctionIcon from "../channels/function-icon.vue";

    export default {
        components: {FunctionIcon},
        props: ['model', 'noLink'],
        computed: {
            caption() {
                return this.model.caption || this.$t('Direct link') + ' ID' + this.model.id;
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'directLink', params: {id: this.model.id}};
            }
        }
    };
</script>

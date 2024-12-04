<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.hidden ? 'grey' : 'green')"
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
            </dl>
            <dl v-if="model.relationsCount">
                <dd>{{ $t("No{'.'} of channels") }}</dd>
                <dt>{{ model.relationsCount.channels }}</dt>
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
                return this.model.caption || this.$t('Channel group') + ' ID' + this.model.id;
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'channelGroup', params: {id: this.model.id}};
            }
        }
    };
</script>

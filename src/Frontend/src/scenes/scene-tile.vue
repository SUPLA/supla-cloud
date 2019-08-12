<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }}</strong></h2>
            </div>
            <dl>
                <dd>{{ $t('No of operations') }}</dd>
                <dt>{{ model.operationsIds.length || 0 }}</dt>
            </dl>
            <div v-if="model.caption">
                <div class="separator"></div>
                {{ model.caption }}
            </div>
        </router-link>
    </square-link>
</template>

<script>
    export default {
        props: ['model', 'noLink'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'scene', params: {id: this.model.id}};
            }
        }
    };
</script>

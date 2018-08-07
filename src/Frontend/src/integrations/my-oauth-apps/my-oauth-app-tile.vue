<template>
    <square-link class="clearfix pointer lift-up yellow"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <h2 class="pull-left">ID<strong>{{ model.id }} </strong></h2>
            </div>
            <dl class="ellipsis">
                <dd>{{ $t('Public ID') }}</dd>
                <dt>{{ model.publicId | ellipsis }}</dt>
            </dl>
            <div class="separator"></div>
            {{ model.name }}
        </router-link>
    </square-link>
</template>

<script>
    export default {
        props: ['model'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'myOauthApp', params: {id: this.model.id}};
            }
        }
    };
</script>

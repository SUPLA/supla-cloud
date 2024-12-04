<template>
    <square-link :class="'clearfix pointer lift-up with-label ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <span>
                <h3>{{ caption }}</h3>
                <dl>
                    <dd>ID</dd>
                    <dt>{{ model.id }}</dt>
                </dl>
                <dl v-if="model.relationsCount">
                    <dd>{{ $t("No{'.'} of locations") }}</dd>
                    <dt>{{ model.relationsCount.locations }}</dt>
                    <dd>{{ $t("No{'.'} of Client’s apps") }}</dd>
                    <dt>{{ model.relationsCount.clientApps }}</dt>
                </dl>
                <div class="square-link-label">
                    <span :class="'label label-' + (model.enabled ? 'success' : 'grey')">
                        {{ $t(model.enabled ? 'Enabled' : 'Disabled') }}
                    </span>
                </div>
            </span>
        </router-link>
    </square-link>
</template>

<script>
    export default {
        props: ['model', 'noLink'],
        computed: {
            caption() {
                return this.model.caption || this.$t('Access Identifier') + ' ID' + this.model.id;
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'accessId', params: {id: this.model.id}};
            }
        }
    };
</script>

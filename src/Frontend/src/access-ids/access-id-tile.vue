<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <span>
                <h2>ID<strong>{{ model.id }} </strong></h2>
                <dl v-if="model.relationsCount">
                    <dd>{{ $t('No. of locations') }}</dd>
                    <dt>{{ model.relationsCount.locations }}</dt>
                    <dd>{{ $t('No. of Client’s apps') }}</dd>
                    <dt>{{ model.relationsCount.clientApps }}</dt>
                </dl>
                <div v-if="model.caption">
                    <div class="separator"></div>
                    {{ model.caption }}
                </div>
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
            linkSpec() {
                return this.noLink ? {} : {name: 'accessId', params: {id: this.model.id}};
            }
        }
    };
</script>

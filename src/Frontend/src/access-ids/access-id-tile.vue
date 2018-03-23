<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <router-link :to="{name: 'accessId', params: model}">
            <span>
                <h2>ID<strong>{{ model.id }} </strong></h2>
                <dl>
                    <dd>{{ $t('Locations no') }}</dd>
                    <dt>{{ model.locationsIds.length }}</dt>
                    <dd>{{ $t('Client apps no') }}</dd>
                    <dt>{{ model.clientAppsIds.length }}</dt>
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
        props: ['model'],
    };
</script>

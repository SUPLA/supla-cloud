<template>
    <square-link :class="'clearfix pointer with-label ' + (model.enabled ? 'green' : 'grey')"
        @click="$emit('click')">
        <h3>{{ model.caption }}</h3>
        <dl>
            <dd v-if="model.caption != model.name">{{ model.name }} /</dd>
            <dd>{{ model.softwareVersion }} / {{ model.protocolVersion }}</dd>
            <dt></dt>
        </dl>
        <div class="separator invisible"></div>
        <dl>
            <dd>{{ $t('Last access') }}</dd>
            <dt>{{ model.lastAccessDate | formatDateTime }}</dt>
            <dd>{{ $t('from the IP') }}</dd>
            <dt>{{ model.lastAccessIpv4 }}</dt>
        </dl>
        <div class="separator invisible"></div>
        <dl>
            <dd>{{ $t('Access ID') }}</dd>
            <dt>{{ model.accessId ? model.accessId.caption : $t('None') }}</dt>
        </dl>
        <div class="square-link-label">
            <client-app-connection-status-label :app="model"></client-app-connection-status-label>
        </div>
    </square-link>
</template>

<script>
    import ClientAppConnectionStatusLabel from "./client-app-connection-status-label.vue";

    export default {
        props: ['model'],
        components: {ClientAppConnectionStatusLabel},
    };
</script>

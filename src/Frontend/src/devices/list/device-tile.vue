<template>
    <square-link :class="'clearfix pointer ' + (device.enabled ? 'green' : 'grey')">
        <a :href="'iodev/' + device.id + '/view'">
            <h3>{{ device.name }}</h3>
            <dl>
                <dd>{{ device.guid }}</dd>
                <dt></dt>
            </dl>
            <div class="separator invisible"></div>
            <dl>
                <dd>{{ $t('SoftVer') }}</dd>
                <dt>{{ device.softwareVersion }}</dt>
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{device.location.id}} {{ device.location.caption }}</dt>
            </dl>
            <div v-if="device.comment">
                <div class="separator"></div>
                {{ device.comment }}
            </div>
        </a>
    </square-link>
</template>

<script>
    import SquareLink from "src/common/square-link.vue";

    export default {
        props: ['device'],
        components: {SquareLink},
    };
</script>

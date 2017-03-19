<template>
    <span>
        <a :class="'btn btn-default ' + (deviceEnabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading">
            <strong class="clearfix">{{ $t(deviceEnabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (deviceEnabled ? 'DISABLE' : 'ENABLE')) }}
        </a>
        <button-loading v-if="loading"></button-loading>
        <div class="overlay-delete overlay-data overlay-opn">
            <div class="dialog">
                <h1>Harmonogramy</h1>
                <p></p>
                <div class="controls">
                    <a class="overlay-delete-close cancel">{% trans %}CANCEL{% endtrans %}</a>
                    <a href="{ path('_iodev_item_delete', {'id': iodevice.id}) }"
                        class="save"><i class="pe-7s-check"></i></a></div>
            </div>
        </div>
    </span>
</template>

<script>
    import ButtonLoading from "./button-loading.vue";
    import {mapState} from "vuex";

    export default {
        components: {ButtonLoading},
        props: ['deviceId', 'deviceEnabled'],
        data() {
            return {
                loading: false
            };
        },
        mounted() {
            console.log(this);
        },
        methods: {
            toggleEnabled() {
                this.deviceEnabled = !this.deviceEnabled;
            }
        },
        computed: mapState('id', 'enabled', 'name')
    };
</script>

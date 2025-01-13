<template>
    <square-link :class="`clearfix pointer with-label ${backgroundColor}`"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <function-icon :model="model" class="pull-right" width="90"/>
                <h3 class="no-margin-top line-clamp line-clamp-4">{{ caption }}</h3>
            </div>
            <dl class="ellipsis" v-if="model.caption">
                <dd></dd>
                <dt>{{ $t(model.function.caption) }}</dt>
            </dl>
            <dl>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
            </dl>
            <dl class="ellipsis" v-if="device">
                <dd>{{ $t('Device') }}</dd>
                <dt>{{ device.name }}</dt>
            </dl>
            <dl class="ellipsis">
                <dd>{{ $t('Type') }}</dd>
                <dt>{{ $t(model.type.caption) }}</dt>
            </dl>
            <dl class="ellipsis" v-if="location">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{ model.locationId }} {{ location.caption }}</dt>
            </dl>
            <div class="square-link-label">
                <span v-if="model.conflictDetails" class="label label-danger">{{ $t('Conflict') }}</span>
                <ConnectionStatusLabel :model="model"/>
            </div>
            <div class="square-link-label-left" v-if="hasActionTrigger">
                <action-trigger-indicator></action-trigger-indicator>
            </div>
        </router-link>
    </square-link>
</template>

<script setup>
    import FunctionIcon from "./function-icon";
    import ActionTriggerIndicator from "@/channels/action-trigger/action-trigger-indicator";
    import {computed} from "vue";
    import {useLocationsStore} from "@/stores/locations-store";
    import {useI18n} from "vue-i18n";
    import {useDevicesStore} from "@/stores/devices-store";
    import ConnectionStatusLabel from "@/devices/list/connection-status-label.vue";

    const props = defineProps({
        model: Object,
        noLink: {
            type: Boolean,
            default: false,
        }
    });
    const {t} = useI18n();
    const caption = computed(() => props.model.caption || t(props.model.function.caption));
    const linkSpec = computed(() => props.noLink ? {} : {name: 'channel', params: {id: props.model.id}});
    const hasActionTrigger = computed(() => props.model?.relationsCount?.actionTriggers > 0);
    const backgroundColor = computed(() => {
        if (props.model.conflictDetails || props.model.functionId === 0) {
            return 'yellow';
        } else {
            return 'grey';
        }
    });
    const locationsStore = useLocationsStore();
    const location = computed(() => locationsStore.all[props.model.locationId]);
    const devicesStore = useDevicesStore();
    const device = computed(() => devicesStore.all[props.model.iodeviceId]);
</script>

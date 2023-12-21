<template>
    <square-link :class="`clearfix pointer with-label ${model.functionId == 0 ? 'yellow' : 'grey'}`"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <div class="clearfix">
                <function-icon :model="model"
                    class="pull-right"
                    width="90"/>
                <h3 class="no-margin-top line-clamp line-clamp-4">{{ caption }}</h3>
            </div>
            <dl class="ellipsis"
                v-if="model.caption">
                <dd></dd>
                <dt>{{ $t(model.function.caption) }}</dt>
            </dl>
            <dl>
                <dd>ID</dd>
                <dt>{{ model.id }}</dt>
            </dl>
            <dl class="ellipsis"
                v-if="model.iodevice">
                <dd>{{ $t('Device') }}</dd>
                <dt>{{ model.iodevice.name }}</dt>
            </dl>
            <dl class="ellipsis">
                <dd>{{ $t('Type') }}</dd>
                <dt>{{ $t(model.type.caption) }}</dt>
            </dl>
            <dl class="ellipsis"
                v-if="model.location">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{ model.location.id }} {{ model.location.caption }}</dt>
            </dl>
            <div class="square-link-label">
                <connection-status-label :model="model"></connection-status-label>
            </div>
            <div class="square-link-label-left"
                v-if="hasActionTrigger">
                <action-trigger-indicator></action-trigger-indicator>
            </div>
        </router-link>
    </square-link>
</template>

<script setup>
    import FunctionIcon from "./function-icon";
    import ConnectionStatusLabel from "../devices/list/connection-status-label.vue";
    import ActionTriggerIndicator from "@/channels/action-trigger/action-trigger-indicator";
    import {computed, defineProps} from "vue";
    import {i18n} from "@/locale";

    const props = defineProps({
        model: Object,
        noLink: {
            type: Boolean,
            default: false,
        }
    });

    const caption = computed(() => props.model.caption || i18n.t(props.model.function.caption));
    const linkSpec = computed(() => props.noLink ? {} : {name: 'channel', params: {id: props.model.id}});
    const hasActionTrigger = computed(() => props.model?.relationsCount?.actionTriggers > 0);
</script>

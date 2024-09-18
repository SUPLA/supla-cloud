<script setup>
    import {ref} from "vue";
    import {useDebounceFn, useElementBounding} from "@vueuse/core";

    const props = defineProps({
        action: Function,
        labelI18n: {
            type: String,
            default: 'Execute an action',
        },
        successLabelI18n: {
            type: String,
            default: 'Success', // i18n
        }
    });

    const button = ref(null);
    const {width} = useElementBounding(button);

    const fixedWidth = ref(undefined);
    const actionSuccess = ref(false);
    const executingAction = ref(false);
    const clearActionSuccess = useDebounceFn(() => actionSuccess.value = false, 3000);

    function executeAction() {
        if (!executingAction.value && !actionSuccess.value) {
            executingAction.value = true;
            fixedWidth.value = `${width.value}px`;
            props.action()
                .then(() => {
                    actionSuccess.value = true;
                    clearActionSuccess();
                })
                .finally(() => executingAction.value = false);
        }
    }
</script>

<template>
    <button type="button" :class="['btn', actionSuccess ? 'btn-green' : 'btn-white']" @click="executeAction()" ref="button"
        :style="{minWidth: (executingAction || actionSuccess) && fixedWidth || 'auto'}">
        <button-loading-dots v-if="executingAction"/>
        <span v-else-if="actionSuccess">
            <fa icon="check" class="mr-1"/>
            {{ $t(successLabelI18n) }}
        </span>
        <span v-else>{{ $t(labelI18n) }}</span>
    </button>
</template>

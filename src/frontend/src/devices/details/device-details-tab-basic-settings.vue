<template>
    <div>
        <form @submit.prevent="saveChanges()">
            <dl>
                <dt>{{ $t('Name') }}</dt>
                <dd>
                    <input type="text" :disabled="loading" class="form-control" v-model="changes.comment">
                </dd>
            </dl>
            <dl v-if="!device.locked && !device.isVirtual" class="mb-0">
                <dt class="mb-2">{{ $t('Enabled') }}</dt>
                <dd>
                    <toggler v-model="changes.enabled" :disabled="loading"/>
                </dd>
            </dl>
            <SaveCancelChangesButtons :original="{comment: device.comment, enabled: device.enabled}" :changes="changes"
                @cancel="cancelChanges()" @save="saveChanges()"/>
        </form>

        <DependenciesWarningModal
            header-i18n="Some features depend on this device"
            deleting-header-i18n="Turning this device off will result in disabling features listed below."
            removing-header-i18n="Turning this device off will cause its channels not working in the following features."
            v-if="dependenciesThatWillBeDisabled"
            :dependencies="dependenciesThatWillBeDisabled"
            :loading="loading"
            @confirm="saveChanges(false)"
            @cancel="dependenciesThatWillBeDisabled = undefined"/>
    </div>
</template>

<script setup>
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {onMounted, reactive, ref, watch} from "vue";
    import {devicesApi} from "@/api/devices-api";
    import {useChannelsStore} from "@/stores/channels-store";
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";

    const props = defineProps({device: Object});

    const changes = reactive({
        comment: props.device.comment,
        enabled: props.device.enabled,
    });

    const loading = ref(false);
    const dependenciesThatWillBeDisabled = ref(undefined);

    function cancelChanges() {
        changes.comment = props.device.comment;
        changes.enabled = props.device.enabled;
    }

    onMounted(cancelChanges);
    watch(() => props.device, () => cancelChanges());

    async function saveChanges(safe = true) {
        loading.value = true;
        dependenciesThatWillBeDisabled.value = undefined;
        try {
            await devicesApi.update(props.device.id, changes, safe);
            await useChannelsStore().refetchAll();
            cancelChanges();
        } catch (error) {
            if (error.status === 409) {
                dependenciesThatWillBeDisabled.value = error.body;
            }
        } finally {
            loading.value = false;
        }
    }
</script>

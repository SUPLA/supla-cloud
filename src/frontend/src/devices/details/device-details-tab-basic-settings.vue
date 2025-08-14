<template>
    <div>
        <form @submit.prevent="saveChanges()">
            <dl>
                <dt>{{ $t('Name') }}</dt>
                <dd>
                    <input type="text" :disabled="loading" class="form-control" v-model="changes.comment">
                </dd>
            </dl>
            <dl v-if="!device.locked && !device.isVirtual">
                <dt class="mb-2">{{ $t('Enabled') }}</dt>
                <dd>
                    <toggler v-model="changes.enabled" :disabled="loading"/>
                </dd>
            </dl>
            <transition-expand>
                <div class="text-center mt-3" v-if="hasPendingChanges">
                    <a class="btn btn-grey mx-1"
                        @click="cancelChanges()">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel changes') }}
                    </a>
                    <button class="btn btn-white mx-1" type="submit" :disabled="loading">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save changes') }}
                    </button>
                </div>
            </transition-expand>
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
    import {computed, onMounted, reactive, ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {devicesApi} from "@/api/devices-api";
    import {useChannelsStore} from "@/stores/channels-store";

    const props = defineProps({device: Object});

    const changes = reactive({
        comment: props.device.comment,
        enabled: props.device.enabled,
    });

    const loading = ref(false);
    const dependenciesThatWillBeDisabled = ref(undefined);
    const hasPendingChanges = computed(() => changes.comment !== props.device.comment || changes.enabled !== props.device.enabled);

    function cancelChanges() {
        changes.comment = props.device.comment;
        changes.enabled = props.device.enabled;
    }

    onMounted(cancelChanges);

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

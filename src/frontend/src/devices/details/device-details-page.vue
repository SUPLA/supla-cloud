<template>
    <PageContainer :error="device || loading ? null : 404">
        <div v-if="device">
            <div class="container mt-3">
                <div class="d-flex mb-3">
                    <div class="flex-grow-1">
                        <div v-if="device.comment">
                            <h1 v-title class="mt-0">{{ device.comment }}</h1>
                            <h4>{{ device.name }}</h4>
                        </div>
                        <div v-else>
                            <h1 v-title class="mt-0">{{ device.name }}</h1>
                        </div>
                    </div>
                    <div>
                        <ConnectionStatusLabel :model="device"/>
                        <a class="btn btn-danger ml-3" @click="deleteConfirm = true">
                            {{ $t('Delete') }}
                        </a>
                    </div>
                </div>
            </div>
            <DeviceDetailsTabs :device="device" v-if="device"/>
        </div>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteDevice()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure?')"
            :loading="loading">
            <p>{{ $t('Confirm if you want to remove {deviceName} device and all of its channels.', {deviceName: device.name}) }}</p>
        </modal-confirm>
        <DependenciesWarningModal
            header-i18n="Some features depend on this device"
            description-i18n="Some of the features you have configured depend on channels from this device."
            deleting-header-i18n="The following items will be deleted with this device:"
            removing-header-i18n="The following items use the channels of these device. These references will be also removed."
            :loading="loading"
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteDevice(false)"
            @cancel="dependenciesThatPreventsDeletion = undefined"/>
    </PageContainer>
</template>

<script setup>
    import {useDevicesStore} from "@/stores/devices-store";
    import {computed, ref} from "vue";
    import PageContainer from "@/common/pages/page-container.vue";
    import DeviceDetailsTabs from "@/devices/details/device-details-tabs.vue";
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {useRouter} from "vue-router/composables";
    import ConnectionStatusLabel from "@/devices/list/connection-status-label.vue";

    const props = defineProps({id: Number});

    const devicesStore = useDevicesStore();
    const router = useRouter();

    const device = computed(() => devicesStore.all[props.id]);

    const loading = ref(false);
    const deleteConfirm = ref(false);
    const dependenciesThatPreventsDeletion = ref();

    async function deleteDevice(safe = true) {
        loading.value = true;
        try {
            await devicesStore.remove(props.id, safe);
            await router.push({name: 'me'});
        } catch (error) {
            if (error.status === 409) {
                dependenciesThatPreventsDeletion.value = error.body;
            }
        } finally {
            loading.value = false;
            deleteConfirm.value = false;
        }
    }
</script>

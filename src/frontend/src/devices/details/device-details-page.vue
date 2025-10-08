<template>
    <PageContainer :error="device || loading ? null : 404">
        <div v-if="device">
          <BreadcrumbList :current="deviceTitle(device)">
            <router-link :to="{name: 'me'}">{{ $t('My SUPLA') }}</router-link>
          </BreadcrumbList>
            <div class="container mt-3">
                <div class="d-flex mb-3 flex-wrap">
                    <div class="flex-grow-1">
                        <h1 v-title class="mt-0">{{ deviceTitle(device) }}</h1>
                        <div v-if="device.comment">
                            <h4>{{ device.name }}</h4>
                        </div>
                    </div>
                    <div class="d-flex align-items-flex-start">
                        <DevicePairSubdeviceButton :device="device" class="mb-2"/>
                        <NewVirtualChannelButton v-if="device.isVirtual"/>
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
  import {useRouter} from "vue-router";
  import NewVirtualChannelButton
    from "@/account/integrations/data-sources/new-virtual-channel-button.vue";
  import DevicePairSubdeviceButton from "@/devices/details/device-pair-subdevice-button.vue";
  import ModalConfirm from "@/common/modal-confirm.vue";
  import BreadcrumbList from "@/common/gui/breadcrumb/BreadcrumbList.vue";
  import {deviceTitle} from "@/common/filters.js";

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

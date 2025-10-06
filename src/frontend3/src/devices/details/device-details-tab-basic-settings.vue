<template>
    <div>
        <dl>
            <dt>{{ $t('Name') }}</dt>
            <dd>
                <input type="text" class="form-control" v-model="newConfig.comment">
            </dd>
        </dl>
        <dl v-if="!device.locked && !device.isVirtual" class="mb-0">
            <dt class="mb-2">{{ $t('Enabled') }}</dt>
            <dd>
                <toggler v-model="newConfig.enabled"/>
            </dd>
        </dl>
    </div>
</template>

<script setup>
  import {useDeviceSettingsForm} from "@/devices/details/device-details-helpers";
  import Toggler from "@/common/gui/toggler.vue";

  const props = defineProps({device: Object});

    const emit = defineEmits(['change']);

    const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
        comment: device.comment,
        enabled: device.enabled,
    }))
</script>

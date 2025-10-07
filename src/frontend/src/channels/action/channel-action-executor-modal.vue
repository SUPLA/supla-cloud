<template>
    <modal :header="$t('Execute an action')"
        :display-close-button="true"
        @cancel="$emit('close')">
        <channel-action-executor :subject="subject"/>
        <template #footer><span></span></template>
    </modal>
</template>

<script>
  import ChannelActionExecutor from "./channel-action-executor.vue";
  import Modal from "@/common/modal.vue";

  export default {
        components: {Modal, ChannelActionExecutor},
        props: ['subject'],
    };
</script>

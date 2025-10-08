<template>
  <div>
    <a class="btn btn-default mx-1 my-1" @click="showDownloadConfig = true">
      <fa icon="gear" class="mr-1" />
      {{ $t('Download the history of measurement') }}
    </a>
    <button type="button" class="btn btn-red ml-1 my-1" @click="deleteConfirm = true">
      <i class="pe-7s-trash"></i>
      {{ $t('Delete measurement history') }}
    </button>
    <div class="text-left">
      <modal-confirm
        v-if="deleteConfirm"
        class="modal-warning"
        :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')"
        @confirm="deleteMeasurements()"
        @cancel="deleteConfirm = false"
      />

      <modal v-if="showDownloadConfig" :header="$t('Download measurements history')" display-close-button @cancel="showDownloadConfig = false">
        <ChannelMeasurementsDownloadForm :storage="storage" :date-range="dateRange" :chart-mode="chartMode" @downloaded="showDownloadConfig = false" />
        <template #footer>
          <div></div>
        </template>
      </modal>
    </div>
  </div>
</template>

<script>
  import {successNotification} from '@/common/notifier';
  import {defineAsyncComponent} from 'vue';
  import LoaderDots from '@/common/gui/loaders/loader-dots.vue';
  import {api} from '@/api/api.js';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import Modal from '@/common/modal.vue';

  export default {
    components: {
      Modal,
      ModalConfirm,
      ChannelMeasurementsDownloadForm: defineAsyncComponent({
        loader: () => import(/*webpackChunkName:"measurement-download-form"*/ '@/channels/history/channel-measurements-download-form.vue'),
        loadingComponent: LoaderDots,
      }),
    },
    props: {
      channel: Object,
      storage: Object,
      dateRange: Object,
      chartMode: String,
    },
    data() {
      return {
        deleteConfirm: false,
        showDownloadConfig: false,
      };
    },
    methods: {
      deleteMeasurements() {
        this.deleteConfirm = false;
        api
          .delete_(`channels/${this.channel.id}/measurement-logs`)
          .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')))
          .then(() => this.$emit('delete'));
      },
    },
  };
</script>

<style lang="scss" scoped></style>

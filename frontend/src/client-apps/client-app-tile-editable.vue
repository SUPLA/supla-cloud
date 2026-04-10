<template>
  <div>
    <flipper :flipped="!!editingModel">
      <template #front>
        <client-app-tile :model="app" @click="edit()" />
      </template>
      <template #back>
        <square-link class="yellow not-transform">
          <form v-if="editingModel" @submit.prevent="save()">
            <div class="form-group">
              <label>{{ $t('Name') }}</label>
              <input v-model="editingModel.caption" type="text" class="form-control" />
            </div>
            <div class="form-group">
              <label>{{ $t('Access Identifier') }}</label>
              <div>
                <a @click="assignAccessIds = true">
                  <span v-if="editingModel.accessId"> ID{{ editingModel.accessId.id }} {{ editingModel.accessId.caption }} </span>
                  <span v-else>{{ $t('None') }}</span>
                </a>
              </div>
            </div>
            <Toggler v-model="editingModel.enabled" :label="$t('Enabled')" />
            <div class="form-group text-right">
              <button type="button" :disabled="saving" class="btn btn-danger btn-sm pull-left" @click="deleteConfirm = true">
                {{ $t('Delete') }}
              </button>
              <button type="button" :disabled="saving" class="btn btn-default btn-sm" @click="cancelEdit()">
                {{ $t('Cancel') }}
              </button>
              <button class="btn btn-green btn-sm" type="submit" :disabled="saving">
                <button-loading-dots v-if="saving"></button-loading-dots>
                <span v-else>OK</span>
              </button>
            </div>
          </form>
        </square-link>
      </template>
    </flipper>
    <modal-confirm
      v-if="deleteConfirm"
      :header="$t('Are you sure you want to delete this client?')"
      :loading="saving"
      @confirm="deleteClient()"
      @cancel="deleteConfirm = false"
    >
      <p>{{ $t('The client will be automatically logged out when deleted.') }}</p>
    </modal-confirm>
    <access-id-chooser
      v-if="assignAccessIds"
      title-i18n="Choose Access Identifier"
      :selected="editingModel.accessId"
      @cancel="assignAccessIds = false"
      @confirm="
        editingModel.accessId = $event;
        assignAccessIds = false;
      "
    ></access-id-chooser>
  </div>
</template>

<script>
  import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
  import {successNotification, warningNotification} from '../common/notifier';
  import ClientAppTile from './client-app-tile.vue';
  import AccessIdChooser from '../access-ids/access-id-chooser.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import Toggler from '@/common/gui/toggler.vue';
  import SquareLink from '@/common/tiles/square-link.vue';
  import Flipper from '@/common/tiles/flipper.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {
      Flipper,
      SquareLink,
      Toggler,
      ModalConfirm,
      AccessIdChooser,
      ClientAppTile,
      ButtonLoadingDots,
    },
    props: ['app'],
    data() {
      return {
        saving: false,
        editingModel: null,
        deleteConfirm: false,
        assignAccessIds: false,
      };
    },
    methods: {
      edit() {
        this.editingModel = {...this.app};
      },
      cancelEdit() {
        this.editingModel = null;
      },
      save() {
        this.saving = true;
        const toSend = {...this.editingModel};
        if (toSend.accessId) {
          toSend.accessIdId = toSend.accessId.id;
          delete toSend.accessId;
        }
        api
          .put(`client-apps/${this.app.id}`, toSend)
          .then(() => (this.editingModel = null))
          .then(() => successNotification(this.$t('Data saved')))
          .then(() => this.$emit('change'))
          .finally(() => (this.saving = false));
      },
      deleteClient() {
        this.saving = true;
        api
          .delete_(`client-apps/${this.app.id}`)
          .then(() => (this.editingModel = null))
          .then(() => (this.deleteConfirm = false))
          .then(() => warningNotification(this.$t('Client’s app has been deleted')))
          .then(() => this.$emit('delete'))
          .finally(() => (this.saving = false));
      },
    },
  };
</script>

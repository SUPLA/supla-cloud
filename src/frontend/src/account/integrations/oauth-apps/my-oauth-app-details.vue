<template>
  <page-container :error="error">
    <loading-cover :loading="loading">
      <div v-if="app">
        <div class="container">
          <pending-changes-page
            :header="app.id ? app.name : $t('New OAuth application') + (app.id ? ' ID' + app.id : '')"
            :deletable="!!app.id"
            :is-pending="hasPendingChanges && !isNew"
            @cancel="cancelChanges()"
            @save="saveOauthApp()"
            @delete="deleteConfirm = true"
          >
            <div class="row">
              <div class="col-sm-6" :class="isNew ? 'col-sm-offset-3' : ''">
                <h3 class="text-center">{{ $t('Details') }}</h3>
                <div :class="'hover-editable text-left form-group ' + (isNew ? 'hovered' : '')">
                  <dl>
                    <dd>{{ $t('Name') }}</dd>
                    <dt>
                      <input v-model="app.name" type="text" class="form-control" @keydown="appChanged()" />
                    </dt>
                    <dd>{{ $t('Description') }}</dd>
                    <dt>
                      <textarea v-model="app.description" class="form-control" @keydown="appChanged()"></textarea>
                    </dt>
                    <dd>{{ $t('Authorization callback URLs (one per line)') }}</dd>
                    <dt>
                      <textarea v-model="redirectUris" type="text" class="form-control" @keydown="appChanged()"></textarea>
                    </dt>
                  </dl>
                </div>
                <button v-if="isNew" type="submit" class="btn btn-green" :disabled="loading">
                  <i class="pe-7s-plus"></i>
                  {{ $t('Register a new OAuth application') }}
                </button>
              </div>
              <div v-if="!isNew" class="col-sm-6">
                <h3 class="text-center">{{ $t('Configuration') }}</h3>
                <h4>{{ $t('Public ID') }}</h4>
                <div class="flex-left-full-width">
                  <pre><code>{{ app.publicId }}</code></pre>
                  <copy-button :text="app.publicId"></copy-button>
                </div>
                <h4>{{ $t('Secret') }}</h4>
                <div class="flex-left-full-width">
                  <pre style="user-select: none"><code>{{ secretPreview }}</code></pre>
                  <copy-button :text="app.secret"></copy-button>
                </div>
                <h4>{{ $t('Example Auth URL') }}</h4>
                <div class="flex-left-full-width">
                  <pre><code>{{ exampleAuthUrl }}</code></pre>
                  <copy-button :text="exampleAuthUrl"></copy-button>
                </div>
              </div>
            </div>
          </pending-changes-page>
          <modal-confirm
            v-if="deleteConfirm"
            class="modal-warning"
            :header="$t('Are you sure you want to delete this OAuth application?')"
            :loading="loading"
            @confirm="deleteApp()"
            @cancel="deleteConfirm = false"
          >
          </modal-confirm>
        </div>
      </div>
    </loading-cover>
  </page-container>
</template>

<script>
  import PageContainer from '../../../common/pages/page-container.vue';
  import PendingChangesPage from '../../../common/pages/pending-changes-page.vue';
  import CopyButton from '../../../common/copy-button.vue';
  import {urlParams} from '@/common/utils';
  import {mapState} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import {api} from '@/api/api.js';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';

  export default {
    components: {LoadingCover, ModalConfirm, CopyButton, PageContainer, PendingChangesPage},
    props: ['id'],
    data() {
      return {
        loading: false,
        app: undefined,
        error: false,
        deleteConfirm: false,
        hasPendingChanges: false,
        redirectUris: '',
      };
    },
    mounted() {
      this.fetch();
    },
    methods: {
      fetch() {
        this.hasPendingChanges = false;
        if (this.id && this.id != 'new') {
          this.loading = true;
          this.error = false;
          api
            .get(`oauth-clients/${this.id}?include=secret`, {skipErrorHandler: [403, 404]})
            .then((response) => (this.app = response.body))
            .then(() => (this.redirectUris = this.app.redirectUris.join('\n')))
            .catch((response) => (this.error = response.status))
            .finally(() => (this.loading = false));
        } else {
          this.app = {};
        }
      },
      saveOauthApp() {
        const toSend = {...this.app};
        toSend.redirectUris = this.redirectUris
          .split('\n')
          .map((u) => u.trim())
          .filter((u) => u);
        this.loading = true;
        if (this.isNew) {
          api
            .post('oauth-clients', toSend)
            .then((response) => {
              this.$emit('add', response.body);
            })
            .catch(() => (this.loading = false));
        } else {
          api
            .put('oauth-clients/' + this.app.id, toSend)
            .then((response) => this.$emit('update', response.body))
            .then(() => (this.hasPendingChanges = false))
            .finally(() => (this.loading = false));
        }
      },
      appChanged() {
        this.hasPendingChanges = true;
      },
      deleteApp() {
        this.loading = true;
        api.delete_('oauth-clients/' + this.app.id).then(() => this.$emit('delete'));
        this.app = undefined;
      },
      cancelChanges() {
        this.fetch();
      },
    },
    computed: {
      isNew() {
        return !this.app.id;
      },
      exampleAuthUrl() {
        return (
          this.serverUrl +
          '/oauth/v2/auth?' +
          urlParams({
            client_id: this.app.publicId,
            scope: 'account_r',
            state: 'example-state',
            response_type: 'code',
            redirect_uri: this.app.redirectUris[0],
          })
        );
      },
      secretPreview() {
        const stars20 = '********************';
        return this.app.secret.substr(0, 5) + stars20 + stars20 + this.app.secret.substr(this.app.secret.length - 5);
      },
      ...mapState(useCurrentUserStore, ['serverUrl']),
    },
    watch: {
      id() {
        this.fetch();
      },
    },
  };
</script>

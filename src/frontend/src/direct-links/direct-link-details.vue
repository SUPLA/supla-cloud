<template>
  <PageContainer :error="error">
    <LoadingCover :loading="!ready">
      <div v-if="directLink">
        <div class="container">
          <BreadcrumbList current>
            <RouterLink :to="{name: 'directLinks'}">{{ $t('Direct links') }}</RouterLink>
          </BreadcrumbList>
          <PendingChangesPage
            :header="directLink.id ? directLink.caption || `${$t('Direct link')} ID${directLink.id}` : $t('New direct link')"
            deletable
            :is-pending="dirty"
            @cancel="cancel()"
            @save="save()"
            @delete="deleteDirectLink()"
          >
            <template #deleteConfirm>
              <p>{{ $t('Are you sure you want to delete this direct link?') }}</p>
              <p>{{ $t('You will not be able to generate a direct link with the same URL again.') }}</p>
            </template>
            <div v-if="!directLink.active && directLink.inactiveReason" class="row my-3">
              <div class="col-sm-6 col-sm-offset-3">
                <div class="alert alert-warning">
                  {{ $t('Direct link is not working right now. Reason:') }}
                  <strong>{{ $t(directLink.inactiveReason) }}</strong>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row text-center">
                <div class="col-sm-4">
                  <div class="details-page-block">
                    <h3>{{ $t('Details') }}</h3>
                    <div class="hover-editable text-left">
                      <dl>
                        <dd>{{ $t('Caption') }}</dd>
                        <dt>
                          <input v-model="draft.caption" type="text" class="form-control" />
                        </dt>
                        <dd>{{ $t('Enabled') }}</dd>
                        <dt>
                          <Toggler v-model="draft.enabled" />
                        </dt>
                      </dl>
                      <dl>
                        <dd v-tooltip="$t('Allows to perform an action only using the HTTP PATCH request.')">
                          {{ $t('For devices') }}
                          <i class="pe-7s-help1"></i>
                        </dd>
                        <dt>
                          <toggler v-model="draft.disableHttpGet"></toggler>
                        </dt>
                      </dl>
                      <div v-if="draft.disableHttpGet" class="small">
                        {{
                          $t(
                            'When you execute the link with HTTP PATCH method, you can omit the random part of the link and send it in the request body. This is safer because such request will not be stored in any server or proxy logs, regardless of their configuration. Please find an cURL example request below.'
                          )
                        }}
                        <pre style="margin-top: 5px"><code>{{ examplePatchBody }}</code></pre>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="details-page-block">
                    <h3 class="text-center">{{ $t('actionableSubjectType_' + directLink.subjectType) }}</h3>
                    <div class="text-left">
                      <SubjectTile :model="directLink" />
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="details-page-block">
                    <h3>{{ $t('Execution history') }}</h3>
                    <DirectLinkAudit :direct-link="directLink" />
                  </div>
                </div>
              </div>
            </div>
            <h2>{{ $t('Allowed actions') }}</h2>
            <DirectLinkAllowedActions :direct-link="directLink" v-model="draft.allowedActions" />
            <DirectLinkDetailsConstraints v-model:activeDateRange="draft.activeDateRange" v-model:executionsLimit="draft.executionsLimit" />
            <!--            <direct-link-preview-->
            <!--              v-if="fullUrl"-->
            <!--              :url="fullUrl"-->
            <!--              :direct-link="directLink"-->
            <!--              :possible-actions="possibleActions"-->
            <!--              :allowed-actions="allowedActions"-->
            <!--            ></direct-link-preview>-->
          </PendingChangesPage>
        </div>
      </div>
    </LoadingCover>
  </PageContainer>
</template>

<script setup>
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import {storeToRefs} from 'pinia';
  import {computed, onMounted} from 'vue';
  import PageContainer from '@/common/pages/page-container.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import PendingChangesPage from '@/common/pages/pending-changes-page.vue';
  import {useEditableFields} from '@/common/editable-fields.js';
  import Toggler from '@/common/gui/toggler.vue';
  import SubjectTile from '@/direct-links/subject-tile.vue';
  import DirectLinkAudit from '@/direct-links/direct-link-audit.vue';
  import {useRouter} from 'vue-router';
  import {successNotification} from '@/common/notifier.js';
  import DirectLinkDetailsConstraints from '@/direct-links/direct-link-details-constraints.vue';
  import DirectLinkAllowedActions from '@/direct-links/direct-link-allowed-actions.vue';

  const props = defineProps({id: Number});
  const router = useRouter();

  const directLinksStore = useDirectLinksStore();
  const {ready, all, slugs} = storeToRefs(directLinksStore);
  onMounted(() => directLinksStore.fetchAll());

  const error = computed(() => ready.value && !directLink.value && 404);
  const directLink = computed(() => all.value[props.id]);

  const {draft, dirty, cancel, markSaved, getPatch} = useEditableFields(
    directLink,
    ['caption', 'enabled', 'disableHttpGet', 'executionsLimit', 'activeDateRange', 'allowedActions'],
    {
      mapIn: (src) => ({
        caption: src?.caption ?? '',
        enabled: !!src?.enabled,
        disableHttpGet: !!src?.disableHttpGet,
        executionsLimit: src.executionsLimit,
        activeDateRange: src.activeDateRange,
        allowedActions: src.allowedActions,
      }),
    }
  );

  async function save() {
    await directLinksStore.update(directLink.value.id, {...directLink.value, ...getPatch()});
    markSaved();
  }

  async function deleteDirectLink() {
    await directLinksStore.remove(directLink.value.id);
    successNotification('Direct link deleted successfully.'); // i18n
    await router.push({name: 'directLinks'});
  }

  const urlWithoutSecret = computed(() => '/link/balbla');

  const examplePatchBody = computed(() => {
    return (
      `curl -s -H "Content-Type: application/json" -H "Accept: application/json" -X PATCH ` +
      `-d '{"code":"${'SECRET'}","action":"read"}' ${urlWithoutSecret.value}`
    );
  });
</script>

<!--
<script>
  import Toggler from '../common/gui/toggler.vue';
  import PendingChangesPage from '../common/pages/pending-changes-page.vue';
  import PageContainer from '../common/pages/page-container.vue';
  import ChannelTile from '../channels/channel-tile.vue';
  import SceneTile from '../scenes/scene-tile.vue';
  import ScheduleTile from '../schedules/schedule-list/schedule-tile.vue';
  import ChannelGroupTile from '../channel-groups/channel-group-tile.vue';
  import DirectLinkPreview from './direct-link-preview.vue';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import DirectLinkAudit from './direct-link-audit.vue';
  import SubjectDropdown from '../devices/subject-dropdown.vue';
  import AppState from '../router/app-state';
  import TransitionExpand from '../common/gui/transition-expand.vue';
  import {actionCaption} from '@/channels/channel-helpers';
  import {mapState, mapStores} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';
  import {useChannelsStore} from '@/stores/channels-store.js';
  import {useDevicesStore} from '@/stores/devices-store.js';
  import ActionableSubjectType from '@/common/enums/actionable-subject-type.js';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';

  export default {
    components: {
      BreadcrumbList,
      ModalConfirm,
      LoadingCover,
      TransitionExpand,
      DirectLinkAudit,
      DateRangePicker,
      DirectLinkPreview,
      ChannelTile,
      ScheduleTile,
      SceneTile,
      ChannelGroupTile,
      PageContainer,
      PendingChangesPage,
      Toggler,
    },
    props: ['id', 'item'],
    data() {
      return {
        loading: false,
        error: false,
        deleteConfirm: false,
        hasPendingChanges: false,
        allowedActions: {},
        choosingCustomLimit: false,
      };
    },
    mounted() {
      this.directLinksStore.fetchAll();
    },
    methods: {
      actionCaption,
      fetch() {
        this.hasPendingChanges = false;
        if (this.id && this.id != 'new') {
          this.loading = true;
          this.error = false;
          api
            .get(`direct-links/${this.id}?include=subject`, {skipErrorHandler: [403, 404]})
            .then((response) => (this.directLink = response.body))
            .then(() => this.calculateAllowedActions())
            .catch((response) => (this.error = response.status))
            .finally(() => (this.loading = false));
        } else {
          this.directLink = {};
          const subjectForNewLink = AppState.shiftTask('directLinkCreate');
          if (subjectForNewLink && subjectForNewLink !== 'new') {
            this.chooseSubjectForNewLink(subjectForNewLink);
          }
        }
      },
      calculateAllowedActions() {
        this.allowedActions = {};
        this.possibleActions.forEach((possibleAction) => {
          this.$set(this.allowedActions, possibleAction.name, this.directLink.allowedActions.indexOf(possibleAction.name) >= 0);
        });
      },
      directLinkChanged() {
        this.hasPendingChanges = true;
      },
      saveDirectLink() {
        const toSend = {...this.directLink};
        toSend.allowedActions = this.currentlyAllowedActions;
        this.loading = true;
        api
          .put('direct-links/' + this.directLink.id, toSend)
          .then((response) => {
            this.$emit('update', response.body);
            this.directLink.active = response.body.active;
            this.directLink.inactiveReason = response.body.inactiveReason;
          })
          .then(() => (this.hasPendingChanges = false))
          .finally(() => (this.loading = false));
      },
      deleteDirectLink() {
        this.loading = true;
        api.delete_('direct-links/' + this.directLink.id).then(() => this.$emit('delete'));
        this.directLink = undefined;
      },
      setExecutionsLimit(limit) {
        this.choosingCustomLimit = false;
        this.directLink.executionsLimit = limit;
        this.directLinkChanged();
      },
      cancelChanges() {
        this.fetch();
      },

    },
    computed: {
      ActionableSubjectType() {
        return ActionableSubjectType;
      },
      currentlyAllowedActions() {
        const actions = [];
        for (let action in this.allowedActions) {
          if (this.allowedActions[action]) {
            actions.push(action);
          }
        }
        return actions;
      },
      possibleActions() {
        if (this.directLink && this.directLink.subject) {
          // OPEN and CLOSE actions are not supported for valves via API
          const disableOpenClose = ['VALVEPERCENTAGE'].includes(this.directLink.subject.function.name);
          return [
            {
              id: 1000,
              name: 'READ',
              caption: 'Read',
              nameSlug: 'read',
            },
          ]
            .concat(this.directLink.subject.possibleActions)
            .filter((action) => !disableOpenClose || (action.name != 'OPEN' && action.name != 'CLOSE'));
        }
        return [];
      },
      displayOpeningSensorWarning() {
        const isGate = ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(this.directLink.subject.function.name) >= 0;
        return isGate && (this.currentlyAllowedActions.includes('OPEN') || this.currentlyAllowedActions.includes('CLOSE'));
      },
      fullUrl() {
        return (this.item && this.item.url) || '';
      },
      urlWithoutSecret() {
        return this.serverUrl + '/direct/' + this.directLink.id;
      },
      linkSecret() {
        return this.fullUrl ? this.fullUrl.substr(this.fullUrl.lastIndexOf('/') + 1) : this.$t('YOUR LINK CODE');
      },

      ...mapState(useCurrentUserStore, ['serverUrl']),
      ...mapStores(useChannelsStore, {channels: 'all'}),
      ...mapState(useDevicesStore, {devices: 'all'}),
      ...mapStores(useDirectLinksStore),
      directLink() {
        return this.directLinksStore.all[this.id];
      },
    },
    watch: {
      id() {
        this.fetch();
      },
    },
  };
</script>

<style lang="scss">
  @use '../styles/variables' as *;

  .executions-limit {
    font-size: 3em;
    font-weight: bold;
    color: $supla-orange;
    text-align: center;
    margin-bottom: 10px;
  }
</style>
-->

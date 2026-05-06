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
            @delete="deleteDirectLink($event)"
          >
            <template #deleteConfirm>
              <p>{{ $t('Are you sure you want to delete this direct link?') }}</p>
              <p>{{ $t('You will not be able to generate a direct link with the same URL again.') }}</p>
            </template>
            <div v-if="slugs[directLink.id]" class="alert alert-info d-flex align-items-center">
              <div class="mr-3">
                <fa :icon="faLightbulb" size="2xl" />
              </div>
              <div class="flex-grow-1">
                <p class="display-newlines">{{ $t('directLink_slugInfo') }}</p>
                <p class="text-center link-preview text-monospace">
                  <a :href="directLinkUrl" target="_blank">{{ directLinkUrl }}</a>
                </p>
              </div>
            </div>
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
            <DirectLinkDetailsConstraints v-model:activeDateRange="draft.activeDateRange" v-model:executionsLimit="draft.executionsLimit" />
            <h2>{{ $t('Allowed actions') }}</h2>
            <DirectLinkAllowedActions :direct-link="directLink" v-model="draft.allowedActions" />
          </PendingChangesPage>
        </div>
      </div>
    </LoadingCover>
  </PageContainer>
</template>

<script setup>
  import {useDirectLinks} from '@/stores/direct-links-store.js';
  import {storeToRefs} from 'pinia';
  import {computed} from 'vue';
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
  import {faLightbulb} from '@fortawesome/free-solid-svg-icons';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store.js';

  const props = defineProps({id: Number});
  const router = useRouter();
  const {config} = storeToRefs(useFrontendConfigStore());
  const directLinkUrl = computed(() => {
    return `${config.value.suplaUrl}/direct/${directLink.value.id}/${slugs.value[directLink.value.id]}`;
  });

  const directLinksStore = useDirectLinks();
  const {ready, all, slugs} = storeToRefs(directLinksStore);

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

  async function deleteDirectLink(dialog) {
    await directLinksStore.remove(directLink.value.id);
    successNotification('Direct link deleted successfully.'); // i18n
    dialog.close();
    void router.push({name: 'directLinks'});
  }
</script>

<style scoped>
  .link-preview {
    font-size: 1.5em;
    word-break: break-all;
    overflow-wrap: break-word;
  }
</style>

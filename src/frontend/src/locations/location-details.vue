<template>
  <page-container :error="!location && !loading && 404">
    <loading-cover :loading="loading" class="location-details">
      <div v-if="location">
        <div class="container">
          <pending-changes-page
            :header="location.caption || $t('Location') + ' ID' + location.id"
            :deletable="true"
            :is-pending="hasPendingChanges"
            @cancel="initCfg()"
            @save="saveLocation()"
            @delete="deleteConfirm = true"
          >
            <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-8">
                <div class="hover-editable hovered details-page-block text-left">
                  <dl>
                    <dd>{{ $t('Enabled') }}</dd>
                    <dt class="text-center">
                      <toggler v-model="cfg.enabled"></toggler>
                    </dt>
                    <dd>{{ $t('Location name') }}</dd>
                    <dt>
                      <input v-model="cfg.caption" type="text" class="form-control" />
                    </dt>
                    <dd>{{ $t('Password') }}</dd>
                    <dt>
                      <PasswordDisplay :fetch-password="fetchPassword" editable @change="(password) => (cfg.password = password)" />
                    </dt>
                  </dl>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="details-page-block">
                  <h3>{{ $t('I/O Devices') }} ({{ devices.length }})</h3>
                  <table v-if="devices.length" class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>{{ $t('Name') }}</th>
                        <th>{{ $t('Comment') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="ioDevice in devices" :key="ioDevice.id" v-go-to-link-on-row-click>
                        <td>
                          <router-link :to="{name: 'device', params: {id: ioDevice.id}}">{{ ioDevice.id }} </router-link>
                        </td>
                        <td>{{ ioDevice.name }}</td>
                        <td>{{ ioDevice.comment }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <EmptyListPlaceholder v-else class="inline" />
                </div>
              </div>
              <div class="col-sm-6">
                <div class="details-page-block">
                  <h3>{{ $t('Access Identifiers') }} ({{ cfg.accessIdsIds.length }})</h3>
                  <table v-if="cfg.accessIdsIds.length > 0" class="table table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>{{ $t('Caption') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="aid in cfg.accessIdsIds" :key="aid" v-go-to-link-on-row-click>
                        <td>
                          <router-link :to="{name: 'accessId', params: {id: aid}}">{{ aid }}</router-link>
                        </td>
                        <td>{{ accessIdsMap[aid].caption }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <a @click="assignAccessIds = true">
                    <i class="pe-7s-more"></i>
                    {{ $t('Assign Access Identifiers') }}
                  </a>
                  <AccessIdChooser
                    v-if="assignAccessIds"
                    title-i18n="Choose Access Identifiers to be assigned to this Location"
                    :selected="cfg.accessIdsIds"
                    @cancel="assignAccessIds = false"
                    @confirm="updateAccessIds($event)"
                  />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <div class="details-page-block">
                  <h3>{{ $t('Channel groups') }} ({{ channelGroups.length }})</h3>
                  <table v-if="channelGroups.length" class="table table-hover table-valign-middle">
                    <thead>
                      <tr>
                        <th></th>
                        <th>ID</th>
                        <th>{{ $t('Caption') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="channelGroup in channelGroups" :key="channelGroup.id" v-go-to-link-on-row-click>
                        <td style="width: 45px">
                          <function-icon :model="channelGroup"></function-icon>
                        </td>
                        <td>
                          <router-link :to="{name: 'channelGroup', params: {id: channelGroup.id}}">{{ channelGroup.id }} </router-link>
                        </td>
                        <td>
                          <span v-if="channelGroup.caption">{{ channelGroup.caption }}</span>
                          <em v-else>{{ $t('None') }}</em>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <empty-list-placeholder v-else class="inline"></empty-list-placeholder>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="details-page-block">
                  <h3>{{ $t('Channels') }} ({{ channels.length }})</h3>
                  <table class="table table-hover table-valign-middle" v-if="channels.length">
                    <thead>
                      <tr>
                        <th></th>
                        <th>ID</th>
                        <th>{{ $t('Caption') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="channel in channels" :key="channel.id" v-go-to-link-on-row-click>
                        <td style="width: 45px">
                          <function-icon :model="channel"></function-icon>
                        </td>
                        <td>
                          <router-link :to="{name: 'channel', params: {id: channel.id}}">{{ channel.id }}</router-link>
                        </td>
                        <td>{{ channelTitle(channel) }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <empty-list-placeholder v-else class="inline"></empty-list-placeholder>
                </div>
              </div>
            </div>
          </pending-changes-page>
        </div>
      </div>
      <modal-confirm
        v-if="deleteConfirm"
        class="modal-warning"
        :header="$t('Are you sure?')"
        :loading="loading"
        @confirm="deleteLocation()"
        @cancel="deleteConfirm = false"
      >
        {{
          $t('Confirm if you want to remove the Location ID{locationId}. You will no longer be able to connect the i/o devices to this Location.', {
            locationId: location.id,
          })
        }}
      </modal-confirm>
    </loading-cover>
  </page-container>
</template>

<script setup>
  import {computed, onMounted, ref, watch} from 'vue';
  import Toggler from '../common/gui/toggler.vue';
  import {channelTitle as channelTitleFilter} from '../common/filters';
  import PendingChangesPage from '../common/pages/pending-changes-page.vue';
  import PageContainer from '../common/pages/page-container.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {useLocationsStore} from '@/stores/locations-store.js';
  import {isEqual} from 'lodash';
  import {locationsApi} from '@/api/locations-api.js';
  import PasswordDisplay from '@/common/gui/password-display.vue';
  import AppState from '@/router/app-state.js';
  import {useDevicesStore} from '@/stores/devices-store.js';
  import {storeToRefs} from 'pinia';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import {useAccessIdsStore} from '@/stores/access-ids-store.js';
  import AccessIdChooser from '@/access-ids/access-id-chooser.vue';
  import FunctionIcon from '@/channels/function-icon.vue';
  import {useChannelGroupsStore} from '@/stores/channel-groups-store.js';
  import {useChannelsStore} from '@/stores/channels-store.js';

  const props = defineProps({id: String});
  const emit = defineEmits(['add', 'update', 'delete']);

  const loading = ref(false);
  const deleteConfirm = ref(false);
  const assignAccessIds = ref(false);

  const locationsStore = useLocationsStore();
  const location = computed(() => locationsStore.all[props.id]);
  const fetchPassword = () => locationsApi.getOneWithPassword(props.id).then(({password}) => password);

  const {list: allDevices} = storeToRefs(useDevicesStore());
  const devices = computed(() => allDevices.value.filter((d) => d.locationId === location.value?.id));

  const {list: allAccessIds, all: accessIdsMap} = storeToRefs(useAccessIdsStore());
  const accessIds = computed(() => allAccessIds.value.filter((aid) => !!aid.locations.find((l) => l.id === location.value?.id)));

  useChannelGroupsStore().fetchAll();
  const {list: allChannelGroups} = storeToRefs(useChannelGroupsStore());
  const channelGroups = computed(() => allChannelGroups.value.filter((d) => d.locationId === location.value?.id));

  const {list: allChannels} = storeToRefs(useChannelsStore());
  const channels = computed(() => allChannels.value.filter((d) => d.locationId === location.value?.id));

  const locationConfig = computed(() => ({
    enabled: location.value?.enabled,
    caption: location.value?.caption,
    password: '',
    accessIdsIds: accessIds.value?.map((aid) => aid.id),
  }));

  const cfg = ref({});

  const hasPendingChanges = computed(() => !isEqual(cfg.value, locationConfig.value));
  const initCfg = () => (cfg.value = {...locationConfig.value});

  watch(() => props.id, initCfg, {immediate: true});

  onMounted(async () => {
    if (AppState.shiftTask('locationCreate')) {
      if (props.id === 'new') {
        loading.value = true;
        const newLoc = await locationsApi.create();
        emit('add', newLoc);
      }
    }
  });

  function saveLocation() {
    const toSend = {...cfg.value};
    loading.value = true;
    locationsApi
      .update(location.value.id, toSend)
      .then((newLoc) => locationsStore.updateOne(newLoc))
      .then(() => useAccessIdsStore().fetchAll(true))
      .finally(() => (loading.value = false))
      .finally(() => initCfg());
  }

  function deleteLocation() {
    loading.value = true;
    locationsApi
      .delete_(location.value.id)
      .then(() => emit('delete'))
      .catch(() => (loading.value = false));
  }

  function updateAccessIds(accessIds) {
    cfg.value.accessIdsIds = accessIds.map((aid) => aid.id);
    assignAccessIds.value = false;
  }

  function channelTitle(channel) {
    return channelTitleFilter(channel).replace(/^ID[0-9]+ /, '');
  }
</script>

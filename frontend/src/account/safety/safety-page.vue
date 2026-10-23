<template>
  <div>
    <div class="container">
      <h1 v-title>{{ $t('Security') }}</h1>

      <div class="form-group">
        <div class="btn-filters">
          <div class="btn-group btn-group-filters">
            <router-link :to="{name: 'safety.log'}" class="btn">
              {{ $t('Security log') }}
            </router-link>
            <router-link :to="{name: 'safety.accessTokens'}" class="btn">
              {{ $t('Active sessions') }}
            </router-link>
            <router-link :to="{name: 'safety.authorizedOAuthApps'}" class="btn">
              {{ $t('Applications with access') }}
            </router-link>
            <router-link :to="{name: 'safety.personalTokens'}" class="btn">
              {{ $t('Personal access tokens') }}
            </router-link>
            <router-link v-if="access.hasEntries.value" :to="{name: 'safety.resourceAccess'}" class="btn">
              {{ $t('Resource access') }}
              <span v-if="access.pending.value" class="text-warning ml-1 requests-pending-indicator" :title="$t('Pending decisions need your attention.')">
                <fa :icon="faClock" />
                <span class="sr-only">{{ $t('Pending decisions need your attention.') }}</span>
              </span>
            </router-link>
            <router-link :to="{name: 'safety.changePassword'}" class="btn" v-if="!currentUser.technicalAccess">
              {{ $t('Change Password') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
    <router-view></router-view>
  </div>
</template>

<script setup>
  import {provide} from 'vue';
  import {faClock} from '@fortawesome/free-solid-svg-icons';
  import {useCurrentUserStore} from '@/stores/current-user-store.js';
  import {resourceAccessAvailabilityKey, useResourceAccessAvailability} from '@/devices/details/resource-access-availability';

  defineOptions({compatConfig: {MODE: 3}});
  const currentUser = useCurrentUserStore();
  const access = useResourceAccessAvailability();
  provide(resourceAccessAvailabilityKey, access);
</script>

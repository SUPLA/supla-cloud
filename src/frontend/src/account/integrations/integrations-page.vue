<template>
  <div>
    <div class="container">
      <div class="clearfix left-right-header">
        <div>
          <h1 v-title>{{ $t('Integrations') }}</h1>
          <h4>
            {{
              $t(
                'Thanks to integrations, you can easily add new features to your account using solutions provided by external suppliers, developers or by yourself.'
              )
            }}
          </h4>
        </div>
        <div>
          <a href="https://cloud.supla.org/apps" class="btn btn-white mb-3 mt-4" target="_blank">{{ $t('See available integrations') }}</a>
        </div>
      </div>

      <div class="my-3">
        <ul class="nav nav-tabs">
          <li :class="{active: $route.name === 'integrations.myOauthApps'}">
            <router-link :to="{name: 'integrations.myOauthApps'}" class="">
              <fa :icon="faTrowelBricks" />
              {{ $t('My OAuth apps') }}
            </router-link>
          </li>
          <li :class="{active: $route.name === 'integrations.mqtt'}">
            <router-link v-if="frontendConfig.config.mqttBrokerEnabled" :to="{name: 'integrations.mqtt'}" class="">
              <fa :icon="faPodcast" />
              {{ $t('MQTT Broker') }}
            </router-link>
          </li>
          <li :class="{active: $route.name === 'integrations.dataSources'}">
            <router-link :to="{name: 'integrations.dataSources'}" class="">
              <fa :icon="faDatabase" />
              {{ $t('Data sources') }}
            </router-link>
          </li>
        </ul>
      </div>
    </div>
    <router-view></router-view>
  </div>
</template>

<script setup>
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {faDatabase, faPodcast, faTrowelBricks} from '@fortawesome/free-solid-svg-icons';

  const frontendConfig = useFrontendConfigStore();
</script>

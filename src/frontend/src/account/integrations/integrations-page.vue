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
          <router-link :to="{name: 'integrations.myOauthApps'}" v-slot="{href, navigate, isActive}" custom>
            <li :class="{active: isActive}">
              <a :href="href" @click="navigate">
                <fa :icon="faTrowelBricks" />
                {{ $t('My OAuth apps') }}
              </a>
            </li>
          </router-link>
          <router-link v-if="frontendConfig.config.mqttBrokerEnabled" :to="{name: 'integrations.mqtt'}" v-slot="{href, navigate, isActive}" custom>
            <li :class="{active: isActive}">
              <a :href="href" @click="navigate">
                <fa :icon="faPodcast" />
                {{ $t('MQTT Broker') }}
              </a>
            </li>
          </router-link>
          <router-link :to="{name: 'integrations.dataSources'}" v-slot="{href, navigate, isActive}" custom>
            <li :class="{active: isActive}">
              <a :href="href" @click="navigate">
                <fa :icon="faDatabase" />
                {{ $t('Data sources') }}
              </a>
            </li>
          </router-link>
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

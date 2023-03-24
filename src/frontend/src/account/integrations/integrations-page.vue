<template>
    <div>
        <div class="container">

            <div class="clearfix left-right-header">
                <div>
                    <h1 v-title>{{ $t('Integrations') }}</h1>
                    <h4>{{ $t('Thanks to integrations, you can easily add new features to your account using solutions provided by external suppliers, developers or by yourself.') }}</h4>
                </div>
                <div>
                    <a href="https://cloud.supla.org/apps"
                        class="btn btn-white mb-3 mt-4"
                        target="_blank">{{ $t('See available integrations') }}</a>
                </div>
            </div>

            <div class="form-group">
                <div class="btn-filters">
                    <div class="btn-group btn-group-filters">
                        <router-link :to="{name: 'authorized-oauth-apps'}"
                            class="btn">
                            {{ $t('Authorized OAuth apps') }}
                        </router-link>
                        <router-link :to="{name: 'myOauthApps'}"
                            class="btn">
                            {{ $t('My OAuth apps') }}
                        </router-link>
                        <router-link :to="{name: 'personal-tokens'}"
                            class="btn">
                            {{ $t('Personal access tokens') }}
                        </router-link>
                        <router-link v-if="$frontendConfig.mqttBrokerEnabled"
                            :to="{name: 'mqtt-broker'}"
                            class="btn">
                            {{ $t('MQTT Broker') }}
                        </router-link>
                    </div>
                </div>
            </div>
        </div>
        <router-view></router-view>
    </div>
</template>

<script>
    export default {};
</script>

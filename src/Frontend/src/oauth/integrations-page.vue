<template>
    <div>
        <div class="container">
            <h1 v-title>{{ $t('Integrations') }}</h1>
            <h4>{{$t('With integrations you can easily add features to your account, leveraging solutions provided by another developer or even better - by yourself.')}}</h4>
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
                    </div>
                </div>
            </div>
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    export default {};
</script>

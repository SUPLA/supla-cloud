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
                        <router-link :to="{name: 'safety.changePassword'}" class="btn">
                            {{ $t('Change Password') }}
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

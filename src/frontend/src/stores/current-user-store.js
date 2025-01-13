import {defineStore, getActivePinia} from "pinia";
import Vue, {computed, ref} from "vue";
import {useStorage} from "@vueuse/core";
import {DateTime, Settings} from "luxon";
import {Base64} from "js-base64";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";
import {api} from "@/api/api";
import {useDevicesStore} from "@/stores/devices-store";
import {useChannelsStore} from "@/stores/channels-store";
import {useLocationsStore} from "@/stores/locations-store";

export const useCurrentUserStore = defineStore('currentUser', () => {
    const userToken = useStorage('supla-user-token');
    const filesToken = useStorage('supla-user-token-files');
    const tokenExpiration = useStorage('supla-token-expiration');
    const serverUrl = ref('');
    const userData = ref({});
    const username = computed(() => userData.value?.email);

    const synchronizeAuthState = () => {
        Vue.http.headers.common['Authorization'] = userToken.value ? 'Bearer ' + userToken.value : undefined;
        determineServerUrl();
        Vue.http.options.root = serverUrl.value + '/api';
        Settings.defaultZone = userData.value && userData.value.timezone || 'system';
    }

    const determineServerUrl = () => {
        const frontendConfigStore = useFrontendConfigStore();
        const {actAsBrokerCloud, suplaUrl} = frontendConfigStore.config;
        if (actAsBrokerCloud) {
            serverUrl.value = Base64.decode((userToken.value || '').split('.')[1] || '') || suplaUrl;
        } else {
            serverUrl.value = '';
        }
        if (!serverUrl.value) {
            serverUrl.value = (location && location.origin) || '';
        }
    }

    const authenticate = async (username, password) => {
        const {body} = await api.post('webapp-auth', {username, password}, {skipErrorHandler: [401, 409, 429]});
        handleNewToken(body);
        getActivePinia()._s.forEach(store => store.$reset());
        return await fetchUser();
    }

    const handleNewToken = (body) => {
        userToken.value = body.access_token;
        tokenExpiration.value = DateTime.now()
            .plus({seconds: body.expires_in})
            .startOf('second')
            .toISO({suppressMilliseconds: true});
        filesToken.value = body.download_token;
    }

    const forget = () => {
        userToken.value = null;
        filesToken.value = null;
        tokenExpiration.value = null;
        username.value = undefined;
        userData.value = undefined;
        serverUrl.value = undefined;
        synchronizeAuthState();
    }

    const fetchUser = async () => {
        if (userToken.value) {
            synchronizeAuthState();
            await fetchUserData();
        } else {
            return false;
        }
    };

    const fetchUserData = async () => {
        try {
            const {body} = await api.get('users/current', {skipErrorHandler: [401, 409, 429]});
            await Promise.all([
                useFrontendConfigStore().fetchConfig(),
                useDevicesStore().fetchAll(),
                useChannelsStore().fetchAll(),
                useLocationsStore().fetchAll(),
            ]);
            userData.value = body;
        } catch (error) {
            forget();
        }
    }

    const updateUserLocale = async (lang) => {
        await api.patch('users/current', {locale: lang, action: 'change:userLocale'});
        userData.value = {...userData.value, locale: lang};
    }

    const $reset = () => {
    };

    // TODO the code below tries to sync auth state between tabs, but it prevents login
    // window.addEventListener('storage', (e) => {
    //     if (e.key === 'supla-user-token') {
    //         setTimeout(() => window.location.href = window.location.toString());
    //     }
    // });

    return {$reset, fetchUser, authenticate, updateUserLocale, forget, username, userToken, filesToken, userData, serverUrl};
})

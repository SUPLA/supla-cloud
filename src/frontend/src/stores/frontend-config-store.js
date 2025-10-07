import {defineStore} from "pinia";
import {computed, ref} from "vue";
import {api} from "@/api/api";
import {DateTime, Settings} from "luxon";

export const useFrontendConfigStore = defineStore('frontendConfig', () => {
    const cloudVersion = ref('');
    const frontendVersion = ref(FRONTEND_VERSION); // eslint-disable-line no-undef
    const config = ref({});
    const env = ref('prod');
    const baseUrl = ref('');

    const fetchConfig = async () => {
        const fetchStart = new Date();
        const {body} = await api.get('server-info');
        synchronizeServerTime(fetchStart, body.time);
        cloudVersion.value = body.cloudVersion;
        config.value = body.config;
        env.value = body.env || 'prod';
        baseUrl.value = body.baseUrl || '';
    };

    const synchronizeServerTime = (fetchStart, serverTime) => {
        const theServerTime = DateTime.fromISO(serverTime).toJSDate();
        const offset = theServerTime.getTime() - fetchStart.getTime();
        Settings.now = function () {
            return Date.now() + offset;
        };
    };

    const backendAndFrontendVersionMatches = computed(() => {
        const frontendUnknown = frontendVersion.value === 'UNKNOWN_VERSION';
        const versionMatches = frontendVersion.value.indexOf(cloudVersion.value) === 0;
        return versionMatches || frontendUnknown;
    });

    const $reset = () => {
    };

    return {config, cloudVersion, frontendVersion, env, baseUrl, backendAndFrontendVersionMatches, $reset, fetchConfig};
})

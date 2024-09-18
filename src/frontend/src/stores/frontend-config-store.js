import {defineStore} from "pinia";
import {ref} from "vue";
import {useSuplaApi} from "@/api/use-supla-api";

export const useFrontendConfigStore = defineStore('frontendConfig', () => {
    const {data, execute} = useSuplaApi('server-info', {immediate: false}).json();

    const cloudVersion = ref('');
    const frontendVersion = ref(FRONTEND_VERSION); // eslint-disable-line no-undef
    const config = ref({});
    const env = ref('prod');
    const time = ref(undefined);
    const baseUrl = ref('');

    const fetchConfig = async () => {
        await execute();
        cloudVersion.value = data.value.cloudVersion;
        config.value = data.value.config;
        env.value = data.value.env || 'prod';
        time.value = data.value.time;
        baseUrl.value = data.value.baseUrl || '';
    };

    const $reset = () => {
    };

    return {config, cloudVersion, frontendVersion, env, time, baseUrl, $reset, fetchConfig};
})

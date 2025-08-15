<template>
    <div class="details-tabs">
        <div class="container" v-if="availableTabs.length > 1">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li v-for="tabDefinition in availableTabs" :key="tabDefinition.id"
                        :class="{active: $route.name === tabDefinition.route}">
                        <router-link :to="{name: tabDefinition.route, params: {id: device.id}}">
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count() }})</span>
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
        <RouterView :device="device"/>
    </div>
</template>

<script setup>
    import {computed, onMounted} from "vue";
    import {useRouter} from "vue-router/composables";

    const props = defineProps({device: Object});
    const router = useRouter();

    const availableTabs = computed(() => {
        const tabs = [];
        if (props.device.locked) {
            tabs.push({header: 'Unlock the device', route: 'device.unlock'}); // i18n
            tabs.push({header: 'Device', route: 'device.details'}); // i18n
        } else {
            tabs.push({header: 'Channels', route: 'device.channels'}); // i18n
            tabs.push({header: 'Device', route: 'device.details'}); // i18n
            if (props.device.relationsCount.managedNotifications) {
                tabs.push({header: 'Notifications', route: 'device.notifications'}); // i18n
            }
        }
        return tabs;
    });

    onMounted(() => {
        if (availableTabs.value.length) {
            if (router.currentRoute.name === 'device' || !availableTabs.value.map(t => t.route).includes(router.currentRoute.name)) {
                router.replace({name: availableTabs.value[0].route, params: {id: props.device.id}});
            }
        }
    })
</script>

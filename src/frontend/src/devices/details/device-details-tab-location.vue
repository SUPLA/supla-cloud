<template>
    <div>
        <div>
            <router-link :to="{name: 'location', params: {id: device.originalLocationId}}"
                class="original-location"
                v-if="device.originalLocationId && device.originalLocationId !== device.locationId">
                {{ $t('Original location') }}
                <strong>{{ originalLocation.caption }}</strong>
            </router-link>
            <SquareLocationChooser v-model="location" @chosen="changeLocation($event)"/>
        </div>
        <DependenciesWarningModal
            header-i18n="Are you sure you want to change device’s location?"
            description-i18n="Changing the location will also imply changing the location of the following items."
            deleting-header-i18n=""
            removing-header-i18n=""
            :loading="loading"
            v-if="dependenciesThatWillChangeLocation"
            :dependencies="dependenciesThatWillChangeLocation"
            @cancel="loading = dependenciesThatWillChangeLocation = undefined"
            @confirm="changeLocation(dependenciesThatWillChangeLocation.newLocation, false)"/>
    </div>
</template>

<script setup>
    import DependenciesWarningModal from "@/channels/dependencies/dependencies-warning-modal.vue";
    import {computed, ref} from "vue";
    import {devicesApi} from "@/api/devices-api";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useLocationsStore} from "@/stores/locations-store";
    import SquareLocationChooser from "@/locations/square-location-chooser.vue";

    const props = defineProps({device: Object});

    const locationsStore = useLocationsStore();
    const location = computed(() => locationsStore.all[props.device.locationId] || undefined);
    const originalLocation = computed(() => locationsStore.all[props.device.originalLocationId] || undefined);

    const loading = ref(false);
    const dependenciesThatWillChangeLocation = ref(undefined);

    async function changeLocation(location, safe = true) {
        loading.value = true;
        dependenciesThatWillChangeLocation.value = undefined;
        try {
            await devicesApi.update(props.device.id, {locationId: location.id}, safe);
            await useChannelsStore().refetchAll();
        } catch (error) {
            if (error.status === 409) {
                dependenciesThatWillChangeLocation.value = error.body;
                dependenciesThatWillChangeLocation.value.newLocation = location;
            }
        } finally {
            loading.value = false;
        }
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .original-location {
        display: block;
        padding: 5px;
        background: rgba(193, 209, 81, 0.75);
        color: $supla-white;
        margin-bottom: 10px;
        strong {
            display: block;
        }
    }
</style>

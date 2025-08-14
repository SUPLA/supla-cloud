<template>
    <div>
        <div class="list-group" v-if="accessIds.length > 0">
            <router-link :to="{name: 'accessId', params: {id: aid.id}}"
                v-for="aid in accessIds"
                class="list-group-item"
                :key="aid.id">
                ID{{ aid.id }} {{ aid.caption }}
            </router-link>
        </div>
        <div class="list-group" v-else>
            <div class="list-group-item">
                <em>{{ $t('None') }}</em>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import {useAccessIdsStore} from "@/stores/access-ids-store";

    const props = defineProps({device: Object});

    const accessIdsStore = useAccessIdsStore();
    const accessIds = computed(() => accessIdsStore.list.filter((aid) => aid.locations.map((l) => l.id).includes(props.device.locationId)));
</script>

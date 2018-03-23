<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <router-link :to="{name: 'location', params: model}">
            <location-tile-content :location="model"></location-tile-content>
        </router-link>
    </square-link>
</template>

<script>
    import LocationTileContent from "./location-tile-content";

    export default {
        components: {LocationTileContent},
        props: ['model'],
    };
</script>

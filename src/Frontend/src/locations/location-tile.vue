<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <a>
            <location-tile-content :location="model"></location-tile-content>
        </a>
    </square-link>
</template>

<script>
    import LocationTileContent from "./location-tile-content";

    export default {
        components: {LocationTileContent},
        props: ['model'],
    };
</script>

<template>
    <div>
        <square-link :class="'text-left with-label ' + squareLinkClass + ' ' + (value.enabled ? '' : 'grey')"
            v-if="value">
            <a @click="chooseLocation = true">
                <location-tile-content :location="value"></location-tile-content>
            </a>
        </square-link>
        <button class="btn btn-default"
            type="button"
            v-else
            @click="chooseLocation = true">
            {{ $t('choose') }}
        </button>
        <location-chooser v-if="chooseLocation"
            :selected="value"
            @confirm="onLocationChange($event)"
            @cancel="chooseLocation = false"
            class="text-left"></location-chooser>
    </div>
</template>

<script>
    import LocationTileContent from "./location-tile-content";
    import LocationChooser from "./location-chooser";

    export default {
        components: {LocationChooser, LocationTileContent},
        props: ['value', 'squareLinkClass'],
        data() {
            return {
                chooseLocation: false
            };
        },
        methods: {
            onLocationChange(location) {
                this.chooseLocation = false;
                this.$emit('input', location);
            }
        }
    };
</script>



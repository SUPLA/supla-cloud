<template>
    <div>
        <square-link :class="'text-left with-label ' + squareLinkClass + ' ' + (value.enabled ? '' : 'grey')"
            v-if="value"
            @click="chooseLocation = !disabled">
            <location-tile-content :location="value"></location-tile-content>
        </square-link>
        <button class="btn btn-default"
            type="button"
            v-else
            @click="chooseLocation = !disabled">
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
        props: ['value', 'squareLinkClass', 'disabled'],
        data() {
            return {
                chooseLocation: false
            };
        },
        methods: {
            onLocationChange(location) {
                this.chooseLocation = false;
                this.$emit('chosen', location);
            }
        }
    };
</script>



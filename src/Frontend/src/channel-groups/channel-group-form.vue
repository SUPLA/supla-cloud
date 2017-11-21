<template>
    <div>
        <h2>{{ $t(channelGroup.id ? 'Channel group ID' + channelGroup.id : 'New channel group') }}</h2>
        <form @submit.prevent="saveChannelGroup()">
            <input type="text"
                v-model="channelGroup.caption">
            <button type="submit">Zapisz</button>
        </form>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters.vue";
    import ChannelGroupsCarousel from "./channel-groups-carousel.vue";
    import LoaderDots from "../common/loader-dots.vue";
    import latinize from "latinize";
    import Vue from "vue";

    export default {
        props: ['channelGroup'],
        methods: {
            saveChannelGroup() {
                this.$http.post('channel-groups', this.channelGroup);
            }
        }
    };
</script>

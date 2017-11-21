<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ $t('Channel groups') }}</h1>
                <div v-if="channelGroups">
                    <channel-groups-carousel :channel-groups="channelGroups"
                        @select="channelGroupChanged"></channel-groups-carousel>
                    {{ channelGroup ? channelGroup.id : 'nie ma' }}
                </div>
                <loader-dots v-else></loader-dots>
            </div>
        </div>
    </div>
</template>

<script>
    import ChannelGroupsCarousel from "./channel-groups-carousel.vue";
    import LoaderDots from "../common/loader-dots.vue";

    export default {
        components: {ChannelGroupsCarousel, LoaderDots},
        data() {
            return {
                channelGroup: undefined,
                channelGroups: undefined,
            };
        },
        mounted() {
            this.$http.get('channel-groups?include=channels').then(({body}) => {
                this.channelGroups = body;
            });
        },
        methods: {
            channelGroupChanged(channelGroup) {
                this.channelGroup = channelGroup;
            }
        }
    };
</script>

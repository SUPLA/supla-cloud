<template>
    <div class="channel-groups-carousel square-links-height-160">
        <carousel :navigation-enabled="true"
            :pagination-enabled="false"
            navigation-next-label="&gt;"
            navigation-prev-label="&lt;"
            :per-page-custom="[[1024, 4], [768, 3], [600, 2], [400, 1]]">
            <slide>
                <square-link :class="'clearfix pointer lift-up black'">
                    <a class="valign-center text-center">
                        <span>
                            <i class="pe-7s-plus"></i><br>
                            {{ $t('Create new channel group') }}
                        </span>
                    </a>
                </square-link>
            </slide>
            <slide v-for="channelGroup in channelGroups"
                :key="channelGroup.id">
                <square-link :class="'clearfix pointer lift-up ' + (channelGroup.enabled ? 'green' : 'grey') + (selectedChannelGroup == channelGroup ? ' selected' : '')">
                    <a @click="onChannelGroupClick(channelGroup)">
                        <h2>ID<strong>{{ channelGroup.id }} </strong></h2>
                        <dl>
                            <dd>{{ $t('Channels no') }}</dd>
                            <dt>{{ channelGroup.channels.length }}</dt>
                        </dl>
                        <div v-if="channelGroup.caption">
                            <div class="separator"></div>
                            {{ channelGroup.caption }}
                        </div>
                    </a>
                </square-link>
            </slide>
        </carousel>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import SquareLink from "src/common/square-link.vue";

    export default {
        components: {Carousel, Slide, SquareLink},
        props: ['channelGroups'],
        data() {
            return {
                selectedChannelGroup: undefined
            };
        },
        methods: {
            onChannelGroupClick(channelGroup) {
                this.selectedChannelGroup = channelGroup;
                this.$emit('select', channelGroup);
            }
        }
    };
</script>

<style lang="scss">
    .channel-groups-carousel {
        .VueCarousel-navigation-button {
            background: black;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: white !important;
            text-align: center;
            font-size: 2em;
            line-height: 1.1em;
            font-family: 'Quicksand';
            &.VueCarousel-navigation-prev {
                left: -5px;
            }
            &.VueCarousel-navigation-next {
                right: -5px;
            }
        }
        .VueCarousel-slide {
            padding: 5px;
        }
        h2 {
            margin-top: 3px;
        }
    }
</style>

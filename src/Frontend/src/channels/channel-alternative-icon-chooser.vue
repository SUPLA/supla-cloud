<template>
    <div>
        <a @click="choosing = true"
            class="btn btn-link">
            {{ $t('Change icon') }}
        </a>
        <modal :header="$t('Select icon')"
            v-if="choosing">
            <channel-alternative-icon-creator v-if="addingNewIcon"
                :model="channel"></channel-alternative-icon-creator>
            <square-links-carousel v-else
                :items="icons"
                :selected="selectedIcon"
                tile="channelAlternativeIconTile"
                @select="choose($event.id)"></square-links-carousel>
            <div slot="footer">
                <a @click="choosing = addingNewIcon = false"
                    class="cancel">
                    <i class="pe-7s-close"></i>
                </a>
            </div>
        </modal>
    </div>
</template>

<script>
    import SquareLinksCarousel from "../common/tiles/square-links-carousel";
    import ChannelAlternativeIconTile from "./channel-alternative-icon-tile";
    import Vue from "vue";
    import ChannelAlternativeIconCreator from "./channel-alternative-icon-creator";

    Vue.component('channelAlternativeIconTile', ChannelAlternativeIconTile);

    export default {
        components: {ChannelAlternativeIconCreator, SquareLinksCarousel},
        props: ['channel'],
        data() {
            return {
                choosing: false,
                icons: [],
                addingNewIcon: false,
                selectedIcon: undefined
            };
        },
        mounted() {
            this.buildIcons();
        },
        methods: {
            choose(index) {
                if (index == 'new') {
                    this.addingNewIcon = true;
                } else {
                    this.channel.altIcon = index;
                    this.selectedIcon = this.icons.find(icon => icon.id == index);
                    this.choosing = false;
                    this.$emit('change');
                }
            },
            buildIcons() {
                this.icons = [];
                if (this.channel) {
                    for (let index = 0; index <= this.channel.function.maxAlternativeIconIndex; index++) {
                        const icon = {channel: this.channel, id: index};
                        this.icons.push(icon);
                        if (index == this.channel.altIcon) {
                            this.selectedIcon = icon;
                        }
                    }
                    this.icons.push({channel: this.channel, id: 'new'});
                }
            }
        },
        watch: {
            channel() {
                this.buildIcons();
            },
            'channel.function.maxAlternativeIconIndex'() {
                this.buildIcons();
            }
        }
    };
</script>

<template>
    <div>
        <a @click="choosing = true"
            v-if="channel.function.maxAlternativeIconIndex > 0"
            class="btn btn-link">
            {{ $t('Change icon') }}
        </a>
        <modal class="modal-location-chooser"
            :header="$t('Select icon')"
            v-if="choosing">
            <square-links-carousel :items="icons"
                square-links-height="auto"
                :selected="selectedIcon"
                tile="channelAlternativeIconTile"
                @select="choose($event.id)"></square-links-carousel>
            <div slot="footer">
                <a @click="choosing = false"
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

    Vue.component('channelAlternativeIconTile', ChannelAlternativeIconTile);

    export default {
        components: {SquareLinksCarousel},
        props: ['channel'],
        data() {
            return {
                choosing: false,
                icons: [],
                selectedIcon: undefined
            };
        },
        mounted() {
            this.buildIcons();
        },
        methods: {
            choose(index) {
                this.channel.altIcon = index;
                this.selectedIcon = this.icons.find(icon => icon.id == index);
                this.choosing = false;
                this.$emit('change');
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
                }
            }
        },
        watch: {
            channel() {
                this.buildIcons();
            }
        }
    };
</script>

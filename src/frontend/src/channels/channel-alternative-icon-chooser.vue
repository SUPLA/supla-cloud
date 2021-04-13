<template>
    <div>
        <a v-if="channel.function.id" @click="openIconDialog()"
            class="btn btn-link">
            {{ $t('Change icon') }}
        </a>

        <modal :header="addingNewIcon ? $t('Add a new icon') : $t('Select icon')"
            v-if="choosing">
            <loading-cover :loading="!icons.length">
                <div v-if="icons.length">
                    <channel-user-icon-creator v-if="addingNewIcon || editingIcon"
                        :icon="editingIcon"
                        :model="channel"
                        @created="choose($event)"
                        @cancel="buildIcons()"></channel-user-icon-creator>
                    <square-links-carousel v-else
                        :items="icons"
                        :selected="selectedIcon"
                        tile="channelAlternativeIconTile"
                        @select="choose($event)"></square-links-carousel>
                </div>
            </loading-cover>
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
    import ChannelUserIconCreator from "./channel-user-icon-creator";

    Vue.component('channelAlternativeIconTile', ChannelAlternativeIconTile);

    export default {
        components: {ChannelUserIconCreator, SquareLinksCarousel},
        props: ['channel'],
        data() {
            return {
                choosing: false,
                addingNewIcon: false,
                editingIcon: undefined,
                selectedIcon: undefined,
                icons: [],
                userIcons: undefined,
            };
        },
        // mounted() {
        //     this.buildIcons();
        // },
        methods: {
            openIconDialog() {
                this.buildIcons();
                this.choosing = true;
            },
            choose(chosenIcon) {
                if (chosenIcon.id == 'new') {
                    this.addingNewIcon = true;
                } else if (chosenIcon.function) {
                    this.channel.userIconId = chosenIcon.id;
                    this.choosing = false;
                    this.$emit('change');
                } else {
                    this.channel.userIconId = undefined;
                    this.channel.altIcon = chosenIcon.index;
                    this.choosing = false;
                    this.$emit('change');
                }
            },
            buildIcons() {
                this.icons = [];
                this.addingNewIcon = false;
                this.editingIcon = undefined;
                if (this.channel) {
                    this.$http.get('user-icons?function=' + this.channel.function.name).then(response => {
                        for (let index = 0; index <= this.channel.function.maxAlternativeIconIndex; index++) {
                            this.icons.push({id: (-index - 1), channel: this.channel, index});
                        }
                        const userIcons = response.body;
                        userIcons.forEach(userIcon => userIcon.edit = () => this.editingIcon = userIcon);
                        this.icons = this.icons.concat(userIcons);
                        this.icons.push({id: 'new', channel: this.channel});
                        this.updateSelectedIcon();
                    });
                }
            },
            updateSelectedIcon() {
                if (this.channel.userIconId) {
                    this.selectedIcon = this.icons.find(icon => icon.id == this.channel.userIconId);
                    if (!this.selectedIcon) {
                        this.channel.userIconId = undefined;
                        this.updateSelectedIcon();
                    }
                } else {
                    this.selectedIcon = this.icons.find(icon => icon.index == this.channel.altIcon);
                }
            }
        },
        watch: {
            // channel() {
            //     this.buildIcons();
            // },
            // 'channel.function.maxAlternativeIconIndex'() {
            //     this.buildIcons();
            // }
        }
    };
</script>

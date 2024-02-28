<template>
    <div>
        <a v-if="channel.function.id"
            @click="openIconDialog()"
            class="btn btn-link">
            {{ $t('Change icon') }}
        </a>

        <modal-confirm :header="addingNewIcon ? $t('Add a new icon') : $t('Select icon')"
            :cancellable="!addingNewIcon" :confirmable="!addingNewIcon"
            @cancel="choosing = addingNewIcon = false"
            @confirm="choose(selectedIcon)"
            v-if="choosing">
            <loading-cover :loading="!icons.length">
                <div v-if="icons.length">
                    <channel-user-icon-creator v-if="addingNewIcon || editingIcon"
                        :icon="editingIcon"
                        :model="channel"
                        @created="choose($event)"
                        @cancel="buildIcons()"></channel-user-icon-creator>
                    <div v-else class="d-flex icons-list flex-wrap justify-content-center">
                        <a v-for="icon in icons" :key="icon.id"
                            :class="{active: selectedIcon.id === icon.id}"
                            @click="selectedIcon = icon">
                            <channel-user-icon-preview :icon="icon" v-if="icon.function"/>
                            <function-icon v-else
                                width="100"
                                :model="channel.function"
                                :config="channel.config"
                                :alternative="icon.index"/>
                        </a>
                        <a @click="addingNewIcon = true"
                            class="valign-center">
                            <i class="pe-7s-plus" style="font-size: 3em"></i>
                            <p>{{ $t('Add a new icon') }}</p>
                        </a>
                    </div>
                </div>
            </loading-cover>
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelUserIconCreator from "./channel-user-icon-creator";
    import FunctionIcon from "@/channels/function-icon.vue";
    import ChannelUserIconPreview from "@/channels/channel-user-icon-preview.vue";

    export default {
        components: {ChannelUserIconPreview, FunctionIcon, ChannelUserIconCreator},
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
        methods: {
            openIconDialog() {
                this.buildIcons();
                this.choosing = true;
            },
            choose(chosenIcon) {
                if (chosenIcon.function) {
                    this.channel.userIconId = chosenIcon.id;
                    this.choosing = false;
                    this.$emit('change');
                } else {
                    this.channel.userIconId = null;
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
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .icons-list {
        gap: 2px;
        > a {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 120px;
            max-height: 100px;
            border: 1px solid $supla-grey-dark;
            padding: 5px;
            position: relative;
            opacity: .8;

            .channel-icon img {
                max-height: 100%;
            }

            &:hover {
                border-color: $supla-green;
                opacity: 1;
            }
            &.active {
                border-color: $supla-black;
                opacity: 1;
                &:after {
                    content: '';
                    position: absolute;
                    width: 30px;
                    height: 31px;
                    background: url('../assets/checked-corner.svg') no-repeat;
                    top: -1px;
                    right: -1px;
                    border-top-right-radius: 3px;
                    transition: all .5s ease-in-out;
                    animation-duration: 0.5s;
                    animation-fill-mode: both;
                    animation-name: fadeIn;
                }
            }
        }
    }
</style>

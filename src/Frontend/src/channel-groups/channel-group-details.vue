<template>
    <loading-cover :loading="loading">
        <!--<form @submit.prevent="saveChannelGroup()">-->
        <div class="btn-group pull-right"
            v-if="!isNewGroup">
            <button class="btn btn-default"
                @click="toggleHidden()">
                {{ $t(channelGroup.hidden ? 'Show in clients' : 'Hide in clients') }}
            </button>
            <button class="btn btn-default"
                @click="toggleEnabled()">
                {{ $t(channelGroup.enabled ? 'Disable' : 'Enable') }}
            </button>
            <button class="btn btn-danger"
                @click="deleteGroup()">
                {{ $t('Delete') }}
            </button>
        </div>
        <h2 class="no-margin-top">{{ $t(channelGroup.id ? 'Channel group ID' + channelGroup.id : 'New channel group') }}</h2>
        <function-icon :model="channelGroup"
            width="100"></function-icon>
        <div class="form-group">
            <label>Group name</label>
            <input type="text"
                class="form-control"
                @change="saveChannelGroup()"
                v-model="channelGroup.caption">
        </div>
        <!--<div class="row">-->
        <!--<div class="col-sm-6">-->
        <!--</div>-->
        <!--</div>-->
        <!--<button type="submit">Zapisz</button>-->
        <!--</form>-->
        <square-links-grid v-if="channelGroup.channels"
            :count="channelGroup.channels.length + 1"
            class="square-links-height-240">
            <div key="new">
                <flipper :flipped="!!addingNewChannel">
                    <square-link class="clearfix pointer black"
                        slot="front">
                        <a class="valign-center text-center"
                            @click="addingNewChannel = true">
                            <span>
                                <i class="pe-7s-plus"></i>
                                <span v-if="isNewGroup">{{ $t('Add the first channel to save the group') }}</span>
                                <span v-else>{{ $t('Add new channel to this group') }}</span>
                            </span>
                        </a>
                    </square-link>
                    <square-link class="clearfix pointer black not-transform"
                        slot="back">
                        <span class="valign-center text-center">
                            <span>
                                <form @submit.prevent="addChannel()"
                                    v-show="!channelsToChoose || channelsToChoose.length !== 0">
                                    <div class="form-group">
                                        <channels-dropdown :params="'include=iodevice,location&io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
                                            v-model="newChannel"
                                            @update="channelsToChoose = $event"
                                            :hidden-channels="channelGroup.channels"></channels-dropdown>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-default pull-left"
                                            type="button"
                                            @click="addingNewChannel = false">
                                            Cancel
                                        </button>
                                        <button class="btn btn-green pull-right"
                                            :disabled="!newChannel"
                                            type="submit">
                                            Add
                                        </button>
                                    </div>
                                </form>
                                <div v-show="channelsToChoose && channelsToChoose.length === 0">
                                    <i class="pe-7s-paint-bucket"></i>
                                    {{ $t('There are no more channels you can add to this group') }}
                                </div>
                            </span>
                        </span>
                    </square-link>
                </flipper>
            </div>
            <div v-for="channel in channelGroup.channels"
                :key="channel.id">
                <channel-tile :channel="channel"
                    @remove="channelGroup.channels.splice(channelGroup.channels.indexOf(channel), 1)"></channel-tile>
            </div>
        </square-links-grid>
    </loading-cover>
</template>

<script>
    import ChannelsDropdown from "src/devices/channels-dropdown.vue";
    import SquareLinksGrid from "src/common/square-links-grid.vue";
    import SquareLink from "src/common/square-link.vue";
    import ChannelTile from "./channel-tile.vue";
    import Flipper from "../common/flipper.vue";
    import Vue from "vue";
    import FunctionIcon from "./function-icon.vue";
    import LoadingCover from "./loading-cover.vue";

    export default {
        props: ['model'],
        components: {LoadingCover, ChannelsDropdown, ChannelTile, SquareLinksGrid, SquareLink, Flipper, FunctionIcon},
        data() {
            return {
                newChannel: undefined,
                addingNewChannel: false,
                channelsToChoose: undefined,
                loading: false,
                channelGroup: {}
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                this.loading = false;
                this.channelGroup = $.extend(true, {}, this.model);
                if (this.channelGroup.id) {
                    this.loading = true;
                    this.$http.get(`channel-groups/${this.channelGroup.id}?include=channels,iodevice,location`)
                        .then(response => this.channelGroup = response.body)
                        .finally(() => this.loading = false);
                }
                else if (!this.channelGroup.channels) {
                    this.$set(this.channelGroup, 'channels', []);
                }
            },
            saveChannelGroup() {
                if (this.channelGroup.channels.length) {
                    const toSend = Vue.util.extend({}, this.channelGroup);
                    this.loading = true;
                    if (this.isNewGroup) {
                        this.$http.post('channel-groups', toSend).then(response => {
                            const newGroup = response.body;
                            newGroup.channels = this.channelGroup.channels;
                            this.$emit('add', newGroup);
                        });
                    } else {
                        this.$http
                            .put('channel-groups/' + this.channelGroup.id, toSend)
                            .then(response => this.$emit('update', response.body))
                            .finally(() => this.loading = false);
                    }
                }
            },
            deleteGroup() {
                this.loading = true;
                this.$http.delete('channel-groups/' + this.channelGroup.id).then(() => this.$emit('delete'));
            },
            addChannel() {
                if (this.newChannel) {
                    this.channelGroup.channels.push(this.newChannel);
                    if (this.channelGroup.channels.length == 1 && !this.channelGroup.function) {
                        this.channelGroup.function = this.newChannel.function;
                    }
                    this.newChannel = undefined;
                    this.addingNewChannel = false;
                    this.saveChannelGroup();
                }
            },
            toggleHidden() {
                this.channelGroup.hidden = !this.channelGroup.hidden;
                this.saveChannelGroup();
            },
            toggleEnabled() {
                this.channelGroup.enabled = !this.channelGroup.enabled;
                this.saveChannelGroup();
            }
        },
        computed: {
            isNewGroup() {
                return !this.channelGroup.id;
            }
        },
        watch: {
            model() {
                this.initForModel();
            }
        }
    };
</script>

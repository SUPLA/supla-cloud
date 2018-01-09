<template>
    <loading-cover :loading="loading"
        class="channel-group-details">
        <div v-if="channelGroup">
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
                    @click="deleteConfirm = true">
                    {{ $t('Delete') }}
                </button>
            </div>
            <h2 class="no-margin-top">{{ $t(channelGroup.id ? 'Channel group ID' + channelGroup.id : 'New channel group') }}</h2>

            <div class="row hidden-xs">
                <div class="col-xs-12">
                    <dots-route></dots-route>
                </div>
            </div>
            <div class="form-group">
                <div class="row text-center">
                    <div class="col-sm-4">
                        <h3>{{ $t('Details') }}</h3>
                        <dl class="text-left">
                            <dd>{{ $t('Caption') }}</dd>
                            <dt>{{ channelGroup.caption }}</dt>
                        </dl>
                        <!--<input type="text"-->
                        <!--class="form-control"-->
                        <!--@change="saveChannelGroup()"-->
                        <!--v-model="channelGroup.caption">-->
                    </div>
                    <div class="col-sm-4">
                        <h3>{{ $t('Location') }}</h3>
                        <square-link :class="'text-left ' + (channelGroup.location.enabled ? '' : 'grey')"
                            v-if="channelGroup.location">
                            <a @click="chooseLocation = true">
                                <location-tile-content :location="channelGroup.location"></location-tile-content>
                            </a>
                        </square-link>
                        <button class="btn btn-default"
                            v-else-if="isNewGroup"
                            @click="chooseLocation = true">{{ $t('choose') }}
                        </button>
                        <location-chooser v-if="chooseLocation"
                            :current-location="channelGroup.location"
                            @confirm="onLocationChange($event)"
                            @cancel="chooseLocation = false"
                            class="text-left"></location-chooser>
                    </div>
                    <div class="col-sm-4">
                        <h3>{{ $t('Function') }}</h3>
                        <div v-if="channelGroup.function">
                            <function-icon :model="channelGroup"
                                width="100"></function-icon>
                            <h4>{{ $t(channelGroup.function.caption) }}</h4>
                        </div>
                        <div v-else-if="isNewGroup">
                            <i class="pe-7s-help1"
                                style="font-size: 3em"></i>
                        </div>
                    </div>
                </div>
            </div>

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
                                            <channels-dropdown :params="'include=iodevice,location,function,type&io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
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
                        :removable="channelGroup.channels.length > 1"
                        @remove="channelGroup.channels.splice(channelGroup.channels.indexOf(channel), 1)"></channel-tile>
                </div>
            </square-links-grid>
            <modal-confirm v-if="deleteConfirm"
                @confirm="deleteGroup()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete this channel group?')"
                :loading="loading">
            </modal-confirm>
        </div>
    </loading-cover>
</template>

<script>
    import ChannelsDropdown from "src/devices/channels-dropdown.vue";
    import ChannelTile from "./channel-tile.vue";
    import Vue from "vue";
    import FunctionIcon from "./function-icon.vue";
    import LocationChooser from "../locations/location-chooser.vue";
    import DotsRoute from "../common/gui/dots-route.vue";
    import LocationTileContent from "../locations/location-tile-content.vue";

    export default {
        props: ['model'],
        components: {
            ChannelsDropdown,
            ChannelTile,
            FunctionIcon,
            LocationChooser,
            DotsRoute,
            LocationTileContent
        },
        data() {
            return {
                newChannel: undefined,
                addingNewChannel: false,
                channelsToChoose: undefined,
                loading: false,
                channelGroup: undefined,
                deleteConfirm: false,
                chooseLocation: false
            };
        },
        mounted() {
            this.initForModel();
        },
        methods: {
            initForModel() {
                if (this.model.id) {
                    this.loading = true;
                    this.$http.get(`channel-groups/${this.model.id}?include=channels,iodevice,location,function,type`)
                        .then(response => this.channelGroup = response.body)
                        .finally(() => this.loading = false);
                }
                else {
                    this.channelGroup = $.extend(true, {}, this.model);
                    if (!this.channelGroup.channels) {
                        this.$set(this.channelGroup, 'channels', []);
                    }
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
                this.channelGroup = undefined;
            },
            addChannel() {
                if (this.newChannel) {
                    this.channelGroup.channels.push(this.newChannel);
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
            },
            onLocationChange(location) {
                this.channelGroup.location = location;
                this.chooseLocation = false;
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

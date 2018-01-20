<template>
    <loading-cover :loading="loading"
        class="channel-group-details">
        <div v-if="channelGroup">
            <div class="container">
                <div class="clearfix left-right-header">
                    <h2 class="no-margin-top">
                        {{ $t(channelGroup.id ? 'Channel group' : 'New channel group') }}
                        {{ channelGroup.id ? 'ID'+ channelGroup.id : '' }}
                    </h2>
                    <div class="btn-toolbar no-margin-top"
                        v-if="!isNewGroup">
                        <a class="btn btn-default"
                            @click="toggleHidden()">
                            {{ $t(channelGroup.hidden ? 'Show in clients' : 'Hide in clients') }}
                        </a>
                        <a class="btn btn-default"
                            @click="toggleEnabled()">
                            {{ $t(channelGroup.enabled ? 'Disable' : 'Enable') }}
                        </a>
                        <a class="btn btn-danger"
                            @click="deleteConfirm = true">
                            {{ $t('Delete') }}
                        </a>
                    </div>
                </div>


                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <dots-route></dots-route>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row text-center">
                        <div class="col-sm-4">
                            <h3>{{ $t('Details') }}</h3>
                            <div class="hover-editable text-left">
                                <dl>
                                    <dd>{{ $t('Caption') }}</dd>
                                    <dt>
                                        <input type="text"
                                            class="form-control"
                                            @change="saveChannelGroup()"
                                            v-model="channelGroup.caption">
                                    </dt>
                                </dl>
                            </div>
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
            </div>
            <h3 class="text-center visible-xs">{{ $t('Channels') }}</h3>
            <square-links-grid v-if="channelGroup.channels"
                :count="channelGroup.channels.length + 1"
                class="square-links-height-240">
                <div key="new">
                    <channel-group-new-channel-chooser :channel-group="channelGroup"
                        @add="saveChannelGroup()"></channel-group-new-channel-chooser>
                </div>
                <div v-for="channel in channelGroup.channels"
                    :key="channel.id">
                    <channel-group-channel-tile :channel="channel"
                        :removable="channelGroup.channels.length > 1"
                        @remove="removeChannel(channel)"></channel-group-channel-tile>
                </div>
            </square-links-grid>
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteGroup()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete this channel group?')"
                :loading="loading">
            </modal-confirm>
        </div>
    </loading-cover>
</template>

<script>
    import Vue from "vue";
    import FunctionIcon from "../channels/function-icon.vue";
    import LocationChooser from "../locations/location-chooser.vue";
    import DotsRoute from "../common/gui/dots-route.vue";
    import LocationTileContent from "../locations/location-tile-content.vue";
    import ChannelGroupNewChannelChooser from "./channel-group-new-channel-chooser.vue";
    import ChannelGroupChannelTile from "./channel-group-channel-tile";

    export default {
        props: ['model'],
        components: {
            ChannelGroupChannelTile,
            ChannelGroupNewChannelChooser,
            FunctionIcon,
            LocationChooser,
            DotsRoute,
            LocationTileContent
        },
        data() {
            return {
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
            removeChannel(channel) {
                this.channelGroup.channels.splice(this.channelGroup.channels.indexOf(channel), 1);
                this.saveChannelGroup();
            },
            deleteGroup() {
                this.loading = true;
                this.$http.delete('channel-groups/' + this.channelGroup.id).then(() => this.$emit('delete'));
                this.channelGroup = undefined;
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

<style lang="scss">
    @import "../styles/variables";

    .hover-editable {
        .form-control {
            border-color: transparent;
            box-shadow: none;
            transition: border-color .3s;
        }
        &:hover, .form-control {
            .form-control, &:active, &:focus {
                border-color: $supla-grey-dark;
            }
        }
        dl {
            margin-bottom: 0;
            font-size: .95em;
            dd, dt {
                display: inline-block;
            }
            dt:after {
                display: block;
                content: ' ';
            }
            dt {
                width: calc(100% - 105px);
            }
            dd {
                width: 100px;
            }
        }
    }
</style>

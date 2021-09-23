<template>
    <page-container :error="error">
        <loading-cover :loading="loading"
            class="channel-group-details">
            <div v-if="channelGroup">
                <div class="container">
                    <pending-changes-page :header="(channelGroup.id ? $t('Channel group') : $t('New channel group')) + (channelGroup.id ? ' ID'+ channelGroup.id : '')"
                        @cancel="cancelChanges()"
                        @save="saveChannelGroup()"
                        :deletable="!isNewGroup"
                        @delete="deleteConfirm = true"
                        :is-pending="hasPendingChanges && !isNewGroup">
                        <div class="row hidden-xs"
                            v-if="!isNewGroup">
                            <div class="col-xs-12">
                                <dots-route></dots-route>
                            </div>
                        </div>
                        <div class="form-group"
                            v-if="!isNewGroup">
                            <div class="row text-center">
                                <div class="col-sm-4">
                                    <h3>{{ $t('Details') }}</h3>
                                    <div class="hover-editable text-left">
                                        <dl>
                                            <dd>{{ $t('Caption') }}</dd>
                                            <dt>
                                                <input type="text"
                                                    class="form-control"
                                                    @keydown="channelGroupChanged()"
                                                    v-model="channelGroup.caption">
                                            </dt>
                                            <dd>{{ $t('Show on the Client’s devices') }}</dd>
                                            <dt class="text-center">
                                                <toggler v-model="channelGroup.hidden"
                                                    invert="true"
                                                    @input="channelGroupChanged()"></toggler>
                                            </dt>
                                        </dl>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <h3>{{ $t('Location') }}</h3>
                                    <square-location-chooser v-model="channelGroup.location"
                                        @input="onLocationChange($event)"></square-location-chooser>
                                </div>
                                <div class="col-sm-4">
                                    <h3>{{ $t('Function') }}</h3>
                                    <div v-if="channelGroup.function">
                                        <function-icon :model="channelGroup"
                                            width="100"></function-icon>
                                        <h4>{{ $t(channelGroup.function.caption) }}</h4>
                                        <channel-alternative-icon-chooser :channel="channelGroup"
                                            @change="channelGroupChanged()"></channel-alternative-icon-chooser>
                                    </div>
                                    <div v-else-if="isNewGroup">
                                        <i class="pe-7s-help1"
                                            style="font-size: 3em"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
                <h3 class="text-center visible-xs">{{ $t('Channels') }}</h3>
                <div class="form-group">
                    <square-links-grid v-if="channelGroup.channels"
                        :count="channelGroup.channels.length + 1"
                        class="square-links-height-240">
                        <div key="new">
                            <channel-group-new-channel-chooser :channel-group="channelGroup"
                                @add="channelGroupChanged()"></channel-group-new-channel-chooser>
                        </div>
                        <div v-for="channel in channelGroup.channels"
                            :key="channel.id">
                            <channel-group-channel-tile :channel="channel"
                                :removable="channelGroup.channels.length > 1"
                                @remove="removeChannel(channel)"></channel-group-channel-tile>
                        </div>
                    </square-links-grid>
                </div>
            </div>
            <channel-group-details-tabs :channel-group="channelGroup"
                v-if="!isNewGroup"></channel-group-details-tabs>
        </loading-cover>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteGroup()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this channel group?')"
            :loading="loading">
        </modal-confirm>
        <delete-channel-group-with-dependencies-modal
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteGroup(false)"
            @cancel="dependenciesThatPreventsDeletion = undefined"></delete-channel-group-with-dependencies-modal>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import FunctionIcon from "../channels/function-icon.vue";
    import DotsRoute from "../common/gui/dots-route.vue";
    import ChannelGroupNewChannelChooser from "./channel-group-new-channel-chooser.vue";
    import ChannelGroupChannelTile from "./channel-group-channel-tile";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import ChannelAlternativeIconChooser from "../channels/channel-alternative-icon-chooser";
    import ChannelGroupDetailsTabs from "./channel-group-details-tabs";
    import AppState from "../router/app-state";
    import DeleteChannelGroupWithDependenciesModal from "@/channel-groups/delete-channel-group-with-dependencies-modal";

    export default {
        props: ['id'],
        components: {
            DeleteChannelGroupWithDependenciesModal,
            ChannelGroupDetailsTabs,
            ChannelAlternativeIconChooser,
            PageContainer,
            PendingChangesPage,
            Toggler,
            ChannelGroupChannelTile,
            ChannelGroupNewChannelChooser,
            DotsRoute,
            FunctionIcon,
            SquareLocationChooser,
        },
        data() {
            return {
                loading: false,
                channelGroup: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
                dependenciesThatPreventsDeletion: undefined,
            };
        },
        mounted() {
            this.fetch();
        },
        methods: {
            fetch() {
                this.hasPendingChanges = false;
                if (this.id && this.id != 'new') {
                    this.loading = true;
                    this.error = false;
                    this.$http.get(`channel-groups/${this.id}?include=channels,iodevice,location`, {skipErrorHandler: [403, 404]})
                        .then(response => this.channelGroup = response.body)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                } else {
                    this.channelGroup = {};
                    if (!this.channelGroup.channels) {
                        this.$set(this.channelGroup, 'channels', []);
                    }
                    const channelForNewGroup = AppState.shiftTask('channelGroupCreate');
                    if (channelForNewGroup) {
                        this.channelGroup.channels.push(channelForNewGroup);
                        this.channelGroupChanged();
                    }
                }
            },
            channelGroupChanged() {
                if (this.channelGroup.channels.length) {
                    this.hasPendingChanges = true;
                    if (this.isNewGroup) {
                        this.saveChannelGroup();
                    }
                }
            },
            saveChannelGroup() {
                const toSend = Vue.util.extend({}, this.channelGroup);
                this.loading = true;
                if (this.isNewGroup) {
                    this.$http.post('channel-groups', toSend).then(response => {
                        const newGroup = response.body;
                        newGroup.channels = this.channelGroup.channels;
                        this.$emit('add', newGroup);
                    }).catch(() => this.$emit('delete'));
                } else {
                    this.$http
                        .put('channel-groups/' + this.channelGroup.id, toSend)
                        .then(response => this.$emit('update', response.body))
                        .then(() => this.hasPendingChanges = false)
                        .finally(() => this.loading = false);
                }
            },
            removeChannel(channel) {
                this.channelGroup.channels.splice(this.channelGroup.channels.indexOf(channel), 1);
                this.channelGroupChanged();
            },
            deleteGroup(safe = true) {
                this.loading = true;
                this.$http.delete(`channel-groups/${this.channelGroup.id}?safe=${safe ? '1' : '0'}`, {skipErrorHandler: [409]})
                    .then(() => {
                        this.$emit('delete');
                        this.channelGroup = undefined;
                    })
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);

            },
            onLocationChange(location) {
                this.$set(this.channelGroup, 'location', location);
                this.channelGroupChanged();
            },
            cancelChanges() {
                this.fetch();
            },
        },
        computed: {
            isNewGroup() {
                return !this.channelGroup || !this.channelGroup.id;
            }
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

<template>
    <div v-if="directLink.state"
        class="form-group">
        <h3 class="nocapitalize">{{ subjectCaption }}</h3>
        <div>
            <div style="display: inline-block"
                :key="channel.id"
                v-for="channel in channels">
                <div class="form-group">
                    <function-icon :model="{...channel, state: channelsState[channel.id]}"
                        :title="channel.caption"
                        :user-icon="channel.userIcon"
                        width="100"></function-icon>
                    <channel-state-table :state="channelsState[channel.id]"
                        :channel="channel"></channel-state-table>
                </div>
            </div>
        </div>
        <button v-if="readStateUrl"
            type="button"
            :disabled="refreshingState"
            @click="refreshState()"
            class="btn btn-xs btn-default">
            <i class="pe-7s-refresh-2"></i>
            {{ $t('Refresh') }}
        </button>
    </div>
</template>

<script>
    import FunctionIcon from "../../channels/function-icon";
    import ChannelStateTable from "../../channels/channel-state-table";
    import {channelTitle} from "../../common/filters";

    export default {
        props: ['directLink'],
        components: {ChannelStateTable, FunctionIcon},
        data() {
            return {
                refreshingState: false
            };
        },
        mounted() {
            if (!this.directLink.state) {
                this.refreshState();
            }
        },
        methods: {
            refreshState() {
                this.refreshingState = true;
                this.$http.get(this.readStateUrl)
                    .then(response => this.directLink.state = response.body)
                    .finally(() => this.refreshingState = false);
            }
        },
        computed: {
            currentUrl() {
                return window.location.protocol + "//" + window.location.host + window.location.pathname;
            },
            readStateUrl() {
                if (this.currentUrl.indexOf('/read') > 0) {
                    return this.currentUrl;
                }
                const match = this.currentUrl.match(/.+\/direct\/[0-9]+\/.+\//);
                if (match) {
                    return match[0] + 'read';
                }
                return undefined;
            },
            subjectCaption() {
                return channelTitle(this.directLink.subject).replace(/^ID[0-9]+/, '');
            },
            availableChannelsIds() {
                return this.directLink.state ? Object.keys(this.directLink.state) : [];
            },
            channels() {
                if (this.directLink.subject.ownSubjectType === 'channelGroup') {
                    return this.directLink.channels;
                } else {
                    return [this.directLink.subject];
                }
            },
            channelsState() {
                if (this.directLink.subject.ownSubjectType === 'channelGroup') {
                    return this.directLink.state;
                } else {
                    return {[this.directLink.subject.id]: this.directLink.state};
                }
            }
        }
    };
</script>

<template>
    <div v-if="directLink.state"
        class="form-group">
        <h3 class="nocapitalize">{{ stateCaption }}</h3>
        <div v-if="directLink.subject.subjectType === 'channelGroup'">
            <div style="display: inline-block"
                v-for="channelId in availableChannelsIds">
                <div class="form-group">
                    <function-icon :model="{functionId: directLink.subject.functionId, state: directLink.state[channelId]}"
                        width="100"></function-icon>
                    <channel-state-table :state="directLink.state[channelId]"></channel-state-table>
                </div>
            </div>
        </div>
        <div class="form-group"
            v-else>
            <function-icon :model="{functionId: directLink.subject.functionId, state: directLink.state}"
                width="100"></function-icon>
            <channel-state-table :state="directLink.state"></channel-state-table>
        </div>
        <button type="button"
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
                return this.currentUrl.indexOf('/read') > 0 ? this.currentUrl : this.currentUrl + '/read';
            },
            stateCaption() {
                return channelTitle(this.directLink.subject, this, false).replace(/^ID[0-9]+/, '');
            },
            availableChannelsIds() {
                return this.directLink.state ? Object.keys(this.directLink.state) : [];
            }
        }
    };
</script>

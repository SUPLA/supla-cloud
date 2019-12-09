<template>
    <div>
        <div class="container text-right">
            <a @click="createNewChannelGroup()"
                class="btn btn-green btn-lg">
                <i class="pe-7s-plus"></i> {{ $t('Add new channel group') }}
            </a>
        </div>
        <loading-cover :loading="!channelGroups">
            <div class="container"
                v-show="channelGroups && channelGroups.length">
            </div>
            <div v-if="channelGroups">
                <square-links-grid v-if="channelGroups.length"
                    :count="channelGroups.length"
                    class="square-links-height-160">
                    <div v-for="channelGroup in channelGroups"
                        :key="channelGroup.id">
                        <channel-group-tile :model="channelGroup"></channel-group-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelGroupTile from "./channel-group-tile";
    import AppState from "../router/app-state";

    export default {
        props: ['channel'],
        components: {ChannelGroupTile},
        data() {
            return {
                channelGroups: undefined
            };
        },
        mounted() {
            this.$http.get(`channels/${this.channel.id}/channel-groups`)
                .then(response => this.channelGroups = response.body);
        },
        computed: {
            subjectId() {
                return this.subject.id;
            }
        },
        methods: {
            createNewChannelGroup() {
                AppState.addTask('channelGroupCreate', this.channel);
                this.$router.push({name: 'channelGroup', params: {id: 'new'}});
            }
        }
    };
</script>

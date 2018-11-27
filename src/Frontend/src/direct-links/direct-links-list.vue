<template>
    <div>
        <div class="container text-right">
            <router-link :to="{name: 'directLink', params: {id: 'new'}, query: {subjectType, subjectId}}"
                class="btn btn-green btn-lg">
                <i class="pe-7s-plus"></i> {{ $t('Create new direct link') }}
            </router-link>
        </div>
        <loading-cover :loading="!directLinks">
            <div class="container"
                v-show="directLinks && directLinks.length">
            </div>
            <div v-if="directLinks">
                <square-links-grid v-if="directLinks.length"
                    :count="directLinks.length"
                    class="square-links-height-160">
                    <div v-for="directLink in directLinks"
                        :key="directLink.id">
                        <direct-link-tile :model="directLink"></direct-link-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import DirectLinkTile from "./direct-link-tile";

    export default {
        props: ['subject'],
        components: {DirectLinkTile},
        data() {
            return {
                directLinks: undefined
            };
        },
        mounted() {
            this.$http.get(`direct-links?include=subject&subjectType=${this.subjectType}&subjectId=${this.subjectId}`)
                .then(response => this.directLinks = response.body);
        },
        computed: {
            subjectType() {
                return this.subject.channelsIds ? 'channelGroup' : 'channel';
            },
            subjectId() {
                return this.subject.id;
            }
        }
    };
</script>

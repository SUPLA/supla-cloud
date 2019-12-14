<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1 v-if="!subject"
                        v-title>{{ $t('Direct links') }}</h1>
                </div>
                <div :class="subject ? 'no-margin-top' : ''">
                    <a @click="createNewDirectLink()"
                        class="btn btn-green btn-lg">
                        <i class="pe-7s-plus"></i> {{ $t('Create new direct link') }}
                    </a>
                </div>
            </div>
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
    import changeCase from "change-case";
    import DirectLinkTile from "./direct-link-tile";
    import AppState from "../router/app-state";

    export default {
        props: ['subject'],
        components: {DirectLinkTile},
        data() {
            return {
                directLinks: undefined
            };
        },
        mounted() {
            let endpoint = 'direct-links?include=subject';
            if (this.subject) {
                endpoint = `${changeCase.paramCase(this.subject.subjectType)}s/${this.subject.id}/${endpoint}`;
            }
            this.$http.get(endpoint)
                .then(response => this.directLinks = response.body);
        },
        computed: {
            subjectId() {
                return this.subject.id;
            }
        },
        methods: {
            createNewDirectLink() {
                if (this.subject) {
                    AppState.addTask('directLinkCreate', {type: this.subject.subjectType, id: this.subjectId});
                }
                this.$router.push({name: 'directLink', params: {id: 'new'}});
            }
        }
    };
</script>

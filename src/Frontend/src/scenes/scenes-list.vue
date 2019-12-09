<template>
    <div>
        <div class="container text-right">
            <a @click="createNewScene()"
                class="btn btn-green btn-lg">
                <i class="pe-7s-plus"></i> {{ $t('Create new scene') }}
            </a>
        </div>
        <loading-cover :loading="!scenes">
            <div class="container"
                v-show="scenes && scenes.length">
            </div>
            <div v-if="scenes">
                <square-links-grid v-if="scenes.length"
                    :count="scenes.length"
                    class="square-links-height-160">
                    <div v-for="scene in scenes"
                        :key="scene.id">
                        <scene-tile :model="scene"></scene-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import SceneTile from "./scene-tile";
    import AppState from "../router/app-state";

    export default {
        props: ['subject'],
        components: {SceneTile},
        data() {
            return {
                scenes: undefined
            };
        },
        mounted() {
            this.$http.get(`scenes?${this.subject.subjectType}Id=${this.subjectId}`)
                .then(response => this.scenes = response.body);
        },
        computed: {
            subjectId() {
                return this.subject.id;
            }
        },
        methods: {
            createNewScene() {
                AppState.addTask('sceneCreate', {type: this.subject.subjectType, id: this.subjectId});
                this.$router.push({name: 'scene', params: {id: 'new'}});
            }
        }
    };
</script>

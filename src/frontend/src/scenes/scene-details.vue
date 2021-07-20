<template>
    <page-container :error="error">
        <loading-cover :loading="loading">
            <div v-if="scene">
                <div class="container">
                    <pending-changes-page
                        :header="scene.id ? (scene.caption || `${$t('Scene')} ID${scene.id}`) : $t('New scene')"
                        @cancel="cancelChanges()"
                        @save="saveScene()"
                        :deletable="!isNew"
                        @delete="deleteConfirm = true"
                        :is-pending="hasPendingChanges && (!isNew || scene.operations.length)">
                        <div class="row hidden-xs">
                            <div class="col-xs-12">
                                <dots-route num="2"></dots-route>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <h3 class="text-center">{{ $t('Details') }}</h3>
                                    <div class="hover-editable text-left">
                                        <dl>
                                            <dd>{{ $t('Caption') }}</dd>
                                            <dt>
                                                <input type="text"
                                                    class="form-control"
                                                    @keydown="sceneChanged()"
                                                    v-model="scene.caption">
                                            </dt>
                                            <dd>{{ $t('Enabled') }}</dd>
                                            <dt>
                                                <toggler
                                                    @input="sceneChanged()"
                                                    v-model="scene.enabled"></toggler>
                                            </dt>
                                        </dl>
                                    </div>
                                    <h3 class="text-center">{{ $t('Location') }}</h3>
                                    <div class="form-group text-center">
                                        <square-location-chooser v-model="scene.location"
                                            @input="sceneChanged()"></square-location-chooser>
                                    </div>
                                    <div v-if="scene.id">
                                        <h3 class="text-center">{{ $t('Icon') }}</h3>
                                        <div class="form-group text-center">
                                            <function-icon :model="scene"
                                                width="100"></function-icon>
                                            <channel-alternative-icon-chooser :channel="scene"
                                                @change="sceneChanged()"></channel-alternative-icon-chooser>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h3 class="text-center">{{ $t('Operations') }}</h3>
                                    <scene-operations-editor v-model="scene.operations"
                                        @input="sceneChanged()"></scene-operations-editor>
                                </div>
                            </div>
                        </div>
                    </pending-changes-page>
                </div>
                <scene-details-tabs v-if="!hasPendingChanges && !isNew"
                    :scene="scene"></scene-details-tabs>
            </div>
        </loading-cover>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteScene()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this scene?')"
            :loading="loading">
        </modal-confirm>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import DotsRoute from "../common/gui/dots-route.vue";
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import SceneOperationsEditor from "./scene-operations-editor";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import FunctionIcon from "../channels/function-icon";
    import ChannelAlternativeIconChooser from "../channels/channel-alternative-icon-chooser";
    import SceneDetailsTabs from "./scene-details-tabs";
    import AppState from "../router/app-state";

    export default {
        props: ['id', 'item'],
        components: {
            SceneDetailsTabs,
            ChannelAlternativeIconChooser,
            FunctionIcon,
            SquareLocationChooser,
            SceneOperationsEditor,
            PageContainer,
            PendingChangesPage,
            Toggler,
            DotsRoute,
        },
        data() {
            return {
                loading: false,
                scene: undefined,
                error: false,
                deleteConfirm: false,
                hasPendingChanges: false,
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
                    this.$http.get(`scenes/${this.id}?include=operations,subject,location`, {skipErrorHandler: [403, 404]})
                        .then(response => this.scene = response.body)
                        .catch(response => this.error = response.status)
                        .finally(() => this.loading = false);
                } else {
                    this.scene = {enabled: true, operations: []};
                    const subjectForNewScene = AppState.shiftTask('sceneCreate');
                    if (subjectForNewScene) {
                        this.scene.operations.push({subject: subjectForNewScene, type: subjectForNewScene.subjectType});
                    }
                }
            },
            sceneChanged() {
                this.hasPendingChanges = true;
            },
            saveScene() {
                const toSend = Vue.util.extend({}, this.scene);
                this.loading = true;
                if (this.isNew) {
                    this.$http.post('scenes', toSend)
                        .then(response => this.$emit('add', response.body));
                } else {
                    this.$http
                        .put('scenes/' + this.scene.id + '?include=operations,subject', toSend)
                        .then(response => {
                            this.$emit('update', response.body);
                            this.scene.operations = response.body.operations;
                        })
                        .then(() => this.hasPendingChanges = false)
                        .finally(() => this.loading = false);
                }
            },
            deleteScene() {
                this.loading = true;
                this.$http.delete('scenes/' + this.scene.id).then(() => this.$emit('delete'));
                this.scene = undefined;
            },
            cancelChanges() {
                this.fetch();
            },
        },
        computed: {
            isNew() {
                return !this.scene.id;
            },
        },
        watch: {
            id() {
                this.fetch();
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

</style>
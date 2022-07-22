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
                        :is-pending="hasPendingChanges">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="details-page-block">
                                        <h3 class="text-center">{{ $t('Details') }}</h3>
                                        <div class="hover-editable hovered text-left">
                                            <dl>
                                                <dd>{{ $t('Caption') }}</dd>
                                                <dt>
                                                    <input type="text"
                                                        class="form-control"
                                                        @keydown="sceneChanged()"
                                                        v-model="scene.caption">
                                                </dt>
                                            </dl>
                                            <dl class="mt-2">
                                                <dd>{{ $t('Enabled') }}</dd>
                                                <dt>
                                                    <toggler
                                                        @input="sceneChanged()"
                                                        v-model="scene.enabled"></toggler>
                                                </dt>
                                            </dl>
                                        </div>
                                    </div>
                                    <div class="details-page-block text-center">
                                        <h3 class="text-center">{{ $t('Location') }}</h3>
                                        <square-location-chooser v-model="scene.location"
                                            :disabled="hasPendingChanges"
                                            @input="sceneChanged()"></square-location-chooser>
                                    </div>
                                    <div v-if="scene.id"
                                        class="details-page-block">
                                        <h3 class="text-center">{{ $t('Icon') }}</h3>
                                        <div class="form-group text-center">
                                            <function-icon :model="scene"
                                                width="100"></function-icon>
                                            <channel-alternative-icon-chooser
                                                :channel="scene"
                                                @change="sceneChanged()"/>
                                        </div>
                                    </div>
                                    <div v-if="scene.id"
                                        class="details-page-block">
                                        <h3 class="text-center">{{ $t('Actions') }}</h3>
                                        <div class="pt-3">
                                            <channel-action-executor :subject="scene"
                                                :disabled="hasPendingChanges"/>
                                            <div class="alert alert-warning text-center m-0"
                                                v-if="hasPendingChanges">
                                                {{ $t('Save or discard configuration changes first.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="">
                                        <h3 class="text-center">{{ $t('Operations') }}</h3>
                                        <scene-operations-editor
                                            :key="'scene-' + scene.id"
                                            v-model="scene.operations"
                                            @input="sceneChanged()"></scene-operations-editor>
                                    </div>
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
        <dependencies-warning-modal
            header-i18n="Some features depend on this scene"
            deleting-header-i18n="The items below rely on this scene, so they will be deleted."
            removing-header-i18n="Reference to the scene will be removed from the items below."
            v-if="dependenciesThatPreventsDeletion"
            :dependencies="dependenciesThatPreventsDeletion"
            @confirm="deleteScene(false)"
            @cancel="dependenciesThatPreventsDeletion = undefined"/>
    </page-container>
</template>

<script>
    import Vue from "vue";
    import Toggler from "../common/gui/toggler";
    import PendingChangesPage from "../common/pages/pending-changes-page";
    import PageContainer from "../common/pages/page-container";
    import SceneOperationsEditor from "./scene-operations-editor";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import FunctionIcon from "../channels/function-icon";
    import ChannelAlternativeIconChooser from "../channels/channel-alternative-icon-chooser";
    import SceneDetailsTabs from "./scene-details-tabs";
    import AppState from "../router/app-state";
    import ChannelActionExecutor from "../channels/action/channel-action-executor";
    import DependenciesWarningModal from "../channels/dependencies/dependencies-warning-modal";

    export default {
        props: ['id', 'item'],
        components: {
            DependenciesWarningModal,
            ChannelActionExecutor,
            SceneDetailsTabs,
            ChannelAlternativeIconChooser,
            FunctionIcon,
            SquareLocationChooser,
            SceneOperationsEditor,
            PageContainer,
            PendingChangesPage,
            Toggler,
        },
        data() {
            return {
                loading: false,
                scene: undefined,
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
                        .then(response => this.$emit('add', response.body))
                        .finally(() => this.loading = false);
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
            deleteScene(safe = true) {
                this.loading = true;
                this.$http.delete(`scenes/${this.scene.id}?safe=${safe ? 1 : 0}`, {skipErrorHandler: [409]})
                    .then(() => {
                        this.scene = undefined;
                        this.$emit('delete');
                    })
                    .catch(({body, status}) => {
                        if (status === 409) {
                            this.dependenciesThatPreventsDeletion = body;
                        }
                    })
                    .finally(() => this.loading = this.deleteConfirm = false);
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

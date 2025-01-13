<template>
    <div>
        <draggable v-model="operations"
            handle=".timeline-badge"
            direction="vertical"
            animation="200"
            class="scene-timeline"
            @start="dragging = true"
            @end="updateModel()">
            <div :class="['timeline-item', {'timeline-item-delay': !operation.subject}]"
                v-for="operation of operations"
                :key="operation.id">
                <template v-if="operation.subject && operation.subjectType !== 'notification'">
                    <div :class="['timeline-badge action', {'warning': !isOperationValid(operation)}]"
                        v-tooltip="!(operation.action && operation.action.id) ? $t('choose the action') : ''">
                        <function-icon :model="operation.subject"
                            width="38"></function-icon>
                    </div>
                    <div :class="['timeline-panel', {'timeline-panel-danger': displayValidationErrors && !isOperationValid(operation)}]">
                        <div class="timeline-heading">
                            <h4 class="timeline-title">
                                {{ channelTitle(operation.subject) }}
                            </h4>
                            <div class="timeline-panel-controls">
                                <div class="controls">
                                    <a @click="deleteOperation(operation)"><i class="glyphicon glyphicon-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-body">
                            <channel-action-chooser :subject="operation.subject"
                                v-model="operation.action"
                                :always-select-first-action="true"
                                @input="updateModel()"
                                :possible-action-filter="possibleActionFilter(operation.subject)">
                            </channel-action-chooser>
                            <div v-if="waitForCompletionAvailable(operation)" class="mt-2">
                                <label class="checkbox2 text-left">
                                    <input type="checkbox"
                                        @change="updateModel()"
                                        v-model="operation.waitForCompletion">
                                    {{ $t('Wait for this scene to finish before proceeding to the next step ({time}).', {time: prettyMilliseconds(operation.subject.estimatedExecutionTime)}) }}
                                </label>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else-if="operation.subject && operation.subjectType === 'notification'">
                    <div :class="['timeline-badge action', {'warning': !isOperationValid(operation)}]">
                        <i class="pe-7s-volume"></i>
                    </div>
                    <div :class="['timeline-panel', {'timeline-panel-danger': displayValidationErrors && !isOperationValid(operation)}]">
                        <div class="timeline-heading">
                            <h4 class="timeline-title">{{ $t('Send a notification') }}</h4>
                            <div class="timeline-panel-controls">
                                <div class="controls">
                                    <a @click="deleteOperation(operation)"><i class="glyphicon glyphicon-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-body">
                            <NotificationForm v-model="operation.action.param"
                                :display-validation-errors="displayValidationErrors"
                                @input="updateModel()"/>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="timeline-badge"><i class="pe-7s-stopwatch"></i></div>
                    <div class="timeline-panel-container">
                        <scene-operation-delay-slider v-model="operation.delayMs"
                            @delete="deleteOperation(operation)"
                            @input="updateModel()"></scene-operation-delay-slider>
                    </div>
                </template>
            </div>
        </draggable>
        <div class="scene-timeline">
            <div class="timeline-item timeline-item-new">
                <div class="timeline-badge"><i class="pe-7s-plus"></i></div>
                <div class="timeline-panel">
                    <div class="timeline-body">
                        <div class="form-group">
                            <label>{{ $t('Add a new action to the scene') }}</label>
                            <subject-dropdown @input="addSceneOperation($event)"
                                clear-on-select
                                disable-notifications
                                channelsDropdownParams="io=output"></subject-dropdown>
                        </div>
                        <div class="form-group">
                            <label>{{ $t('or another element') }}</label>
                            <div>
                                <button type="button"
                                    @click="addDelay()"
                                    class="btn btn-default mr-2">
                                    <i class="pe-7s-stopwatch"></i>
                                    {{ $t('Add a delay') }}
                                </button>
                                <button type="button"
                                    v-if="notificationsEnabled"
                                    @click="addNotification()"
                                    class="btn btn-default">
                                    <i class="pe-7s-volume"></i>
                                    {{ $t('Send a notification') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SubjectDropdown from "../devices/subject-dropdown";
    import FunctionIcon from "../channels/function-icon";
    import {channelTitle, prettyMilliseconds} from "../common/filters";
    import ChannelActionChooser from "../channels/action/channel-action-chooser";
    import draggable from 'vuedraggable';
    import SceneOperationDelaySlider from "./scene-operation-delay-slider";
    import Vue from 'vue';
    import ChannelFunctionAction from "../common/enums/channel-function-action";
    import ActionableSubjectType from "../common/enums/actionable-subject-type";
    import NotificationForm from "@/notifications/notification-form";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    let UNIQUE_OPERATION_ID = 0;

    export default {
        props: {
            value: Array,
            displayValidationErrors: Boolean,
        },
        components: {
            SceneOperationDelaySlider, NotificationForm,
            ChannelActionChooser, FunctionIcon, SubjectDropdown, draggable
        },
        data() {
            return {
                dragging: false,
                lastValue: undefined,
                operations: [],
            };
        },
        mounted() {
            this.buildOperations();
        },
        methods: {
            buildOperations() {
                if (this.value != this.lastValue) {
                    this.operations = [];
                    for (const op of (this.value || [])) {
                        const operation = Vue.util.extend({}, op);
                        operation.action = {id: operation.actionId, param: operation.actionParam};
                        delete operation.actionId;
                        delete operation.actionParam;
                        operation.id = UNIQUE_OPERATION_ID++;
                        if (operation.subject && !operation.subjectType) {
                            operation.subjectType = operation.subject.ownSubjectType;
                        }
                        if (operation.delayMs) {
                            this.operations.push({id: UNIQUE_OPERATION_ID++, delayMs: operation.delayMs});
                        }
                        if (operation.subjectId) {
                            this.operations.push(operation);
                        }
                    }
                }
            },
            deleteOperation(operation) {
                this.operations.splice(this.operations.indexOf(operation), 1);
                this.updateModel();
            },
            addSceneOperation(subject) {
                if (subject) {
                    this.operations.push({id: UNIQUE_OPERATION_ID++, subject, subjectType: subject.ownSubjectType, delayMs: 0});
                }
            },
            channelTitle(subject) {
                return channelTitle(subject);
            },
            possibleActionFilter() {
                return () => true;
            },
            updateModel() {
                this.dragging = false;
                const operations = [];
                let delay = 0;
                for (const op of this.operations) {
                    const operation = Vue.util.extend({}, op);
                    if (operation.subjectType) {
                        operation.delayMs = delay;
                        operation.actionId = operation.action?.id;
                        operation.actionParam = operation.action?.param;
                        operation.isValid = this.isOperationValid(operation);
                        operations.push(operation);
                        delay = 0;
                    } else if (operation.delayMs) {
                        delay += operation.delayMs;
                    }
                }
                if (delay) {
                    operations.push({id: UNIQUE_OPERATION_ID++, delayMs: delay});
                }
                this.lastValue = operations;
                this.$emit('input', operations);
            },
            isOperationValid(operation) {
                return !operation.subjectType ||
                    (!!operation.actionId && ChannelFunctionAction.paramsValid(operation.actionId, operation.actionParam)) ||
                    (!!operation.action && ChannelFunctionAction.paramsValid(operation.action.id, operation.action.param));
            },
            addDelay() {
                this.operations.push({id: UNIQUE_OPERATION_ID++, delayMs: 5000});
                this.updateModel();
            },
            addNotification() {
                this.operations.push({
                    id: UNIQUE_OPERATION_ID++, subject: {}, subjectType: ActionableSubjectType.NOTIFICATION,
                    action: {id: ChannelFunctionAction.SEND, param: {}}
                });
                this.updateModel();
            },
            waitForCompletionAvailable(operation) {
                return operation.subjectType === ActionableSubjectType.SCENE &&
                    operation.action &&
                    [ChannelFunctionAction.EXECUTE, ChannelFunctionAction.INTERRUPT_AND_EXECUTE].includes(operation.action.id);
            },
            prettyMilliseconds(ms) {
                return prettyMilliseconds(ms);
            }
        },
        computed: {
            notificationsEnabled() {
                return this.frontendConfig.notificationsEnabled;
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
        },
        watch: {
            value() {
                this.buildOperations();
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .scene-timeline {
        list-style: none;
        padding: 20px 0 20px;
        position: relative;

        &:before {
            background-color: #eee;
            bottom: 0;
            content: " ";
            left: 50px;
            margin-left: -1.5px;
            position: absolute;
            top: 0;
            width: 3px;
        }

        .timeline-item {
            margin-bottom: 20px;
            position: relative;

            &:before,
            &:after {
                content: " ";
                display: table;
            }

            &:after {
                clear: both;
            }

            > .timeline-panel-container {
                margin-left: 100px;
                margin-top: 30px;
                position: relative;
            }

            > .timeline-panel {
                border-radius: 2px;
                border: 1px solid #d4d4d4;
                box-shadow: 0 1px 2px rgba(100, 100, 100, 0.2);
                margin-left: 100px;
                padding: 20px;
                position: relative;

                &-danger {
                    border-color: $supla-red;
                }

                .timeline-heading {
                    .timeline-panel-controls {
                        position: absolute;
                        right: 8px;
                        top: 5px;
                    }
                }
            }

            .controls {
                display: inline-block;
                padding-right: 5px;
                user-select: none;
                /*border-right: 1px solid #aaa;*/

                a {
                    color: #999;
                    font-size: 11px;
                    padding: 0 5px;

                    &:hover {
                        color: #333;
                        text-decoration: none;
                        cursor: pointer;
                    }
                }
            }

            .timeline-badge {
                background-color: #999;
                border-radius: 50%;
                color: #fff;
                font-size: 1.8em;
                height: 50px;
                width: 50px;
                left: 25px;
                position: absolute;
                text-align: center;
                top: 16px;
                z-index: 100;
                cursor: move;
                cursor: -webkit-grabbing;
                display: flex;
                flex-direction: column;
                justify-content: center;
                overflow: hidden;
                &.primary {
                    background-color: #2e6da4 !important;
                }

                &.action {
                    background-color: $supla-green;
                }

                &.condition {
                    background-color: $supla-yellow;
                }

                &.success {
                    background-color: #3f903f !important;
                }

                &.warning {
                    background-color: #f0ad4e !important;
                }

                &.danger {
                    background-color: #d9534f !important;
                }

                &.info {
                    background-color: #5bc0de !important;
                }

            }

            .timeline-badge + .timeline-panel {
                &:before {
                    border-bottom: 15px solid transparent;
                    border-left: 0;
                    border-right: 15px solid #ccc;
                    border-top: 15px solid transparent;
                    content: " ";
                    display: inline-block;
                    position: absolute;
                    left: -15px;
                    right: auto;
                    top: 26px;
                }

                &-danger:before {
                    border-right-color: $supla-red;
                }

                &:after {
                    border-bottom: 14px solid transparent;
                    border-left: 0 solid $supla-bg;
                    border-right: 14px solid $supla-bg;
                    border-top: 14px solid transparent;
                    content: " ";
                    display: inline-block;
                    position: absolute;
                    left: -14px;
                    right: auto;
                    top: 27px;
                }
            }
        }
    }

    .timeline-title {
        margin-top: 0;
        color: inherit;
        padding-right: 20px;
    }

    .timeline-body {
        > p, > ul {
            margin-bottom: 0;
        }
        > p + p {
            margin-top: 5px;
        }
    }

    .copy {
        position: absolute;
        top: 5px;
        right: 5px;
        color: #aaa;
        font-size: 11px;
        > * {
            color: #444;
        }
    }

    .flip-list-move {
        transition: transform 0.5s;
    }

    .sortable-ghost {
        .timeline-panel {
            opacity: .5;
        }
    }
</style>

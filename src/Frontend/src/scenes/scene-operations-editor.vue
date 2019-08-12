<template>
    <div>
        <!--        <table class="table">-->
        <!--            <thead>-->
        <!--            <tr>-->
        <!--                <th>{{ $t('Subject') }}</th>-->
        <!--                <th>{{ $t('Action') }}</th>-->
        <!--                <th>{{ $t('Delay') }}</th>-->
        <!--            </tr>-->
        <!--            </thead>-->
        <!--            <tbody>-->
        <!--            <tr v-for="operation in scene.operations">-->
        <!--                <td>-->
        <!--                    <function-icon :model="operation.subject"-->
        <!--                        width="40"></function-icon>-->
        <!--                    {{ channelTitle(operation.subject) }}-->
        <!--                </td>-->
        <!--                <td>-->
        <!--                    <channel-action-chooser :subject="operation.subject"-->
        <!--                        v-model="operation.action"-->
        <!--                        :possible-action-filter="possibleActionFilter(operation.subject)"></channel-action-chooser>-->
        <!--                </td>-->
        <!--                <td>-->
        <!--                    <div class="input-group">-->
        <!--                        <input type="number"-->
        <!--                            min="0"-->
        <!--                            class="form-control"-->
        <!--                            maxlength="4"-->
        <!--                            v-model="operation.delay">-->
        <!--                        <span class="input-group-addon">s</span>-->
        <!--                    </div>-->
        <!--                </td>-->
        <!--            </tr>-->
        <!--            </tbody>-->
        <!--        </table>-->

        <draggable v-model="operations"
            handle=".timeline-badge"
            direction="vertical"
            animation="200"
            class="scene-timeline"
            @start="dragging = true"
            @end="dragging = false">
            <div :class="['timeline-item', {'timeline-item-delay': !operation.subject}]"
                v-for="operation of operations"
                :key="operation.id">
                <template v-if="operation.subject">
                    <div class="timeline-badge action">
                        <function-icon :model="operation.subject"
                            width="38"></function-icon>
                    </div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4 class="timeline-title">{{ channelTitle(operation.subject) }}</h4>
                            <div class="timeline-panel-controls">
                                <div class="controls">
                                    <a class="drag-handle"><i class="glyphicon glyphicon-move"></i></a>
                                    <a href="#"><i class="glyphicon glyphicon-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-body drag-handle">
                            <channel-action-chooser :subject="operation.subject"
                                v-model="operation.action"
                                :possible-action-filter="possibleActionFilter">
                            </channel-action-chooser>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="timeline-badge"><i class="pe-7s-stopwatch"></i></div>
                    <div class="timeline-panel-container">
                        <scene-operation-delay-slider v-model="operation.delayMs"></scene-operation-delay-slider>
                    </div>
                </template>
            </div>
        </draggable>
        <div class="form-group">
            <label>{{ $t('Choose item to use in scene') }}</label>
            <subject-dropdown @input="addSceneOperation($event)"
                channelsDropdownParams="io=output"></subject-dropdown>
        </div>
        <!--            <li class="timeline-item"-->
        <!--                style="margin-bottom: 0;">-->
        <!--                <div class="timeline-badge action">-->
        <!--                    <function-icon :model="{id: 20}"-->
        <!--                        width="38"></function-icon>-->
        <!--                </div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Brama wjazdowa ID45</h4>-->
        <!--                        <div class="timeline-panel-controls">-->
        <!--                            <div class="controls">-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-move"></i></a>-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-trash"></i></a>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">Otwórz/zamknij</div>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item">-->
        <!--                <div class="timeline-badge"><i class="pe-7s-stopwatch"></i></div>-->
        <!--                <div class="timeline-separator">-->
        <!--                    20 sekund-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item">-->
        <!--                <div class="timeline-badge action">-->
        <!--                    <function-icon :model="{id: 130}"-->
        <!--                        width="38"></function-icon>-->
        <!--                </div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Podlewajka ogródka ID100</h4>-->
        <!--                        <div class="timeline-panel-controls">-->
        <!--                            <div class="controls">-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-move"></i></a>-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-trash"></i></a>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">-->
        <!--                        <div class="btn-group">-->
        <!--                            <button class="btn btn-white">Wyłącz</button>-->
        <!--                            <button class="btn btn-green">Włącz</button>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item">-->
        <!--                <div class="timeline-badge action">-->
        <!--                    <function-icon :model="{id: 140}"-->
        <!--                        width="38"></function-icon>-->
        <!--                </div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Lampki na podjeździe do mojej rezydencji ID120</h4>-->
        <!--                        <div class="timeline-panel-controls">-->
        <!--                            <div class="controls">-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-move"></i></a>-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-trash"></i></a>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">-->
        <!--                        <div class="btn-group">-->
        <!--                            <button class="btn btn-green">Wyłącz</button>-->
        <!--                            <button class="btn btn-white">Włącz</button>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item">-->
        <!--                <div class="timeline-badge condition">-->
        <!--                    <function-icon :model="{id: 40}"-->
        <!--                        width="38"></function-icon>-->
        <!--                </div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Termometr zewnętrzny ID1230</h4>-->
        <!--                        <div class="timeline-panel-controls">-->
        <!--                            <div class="controls">-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-move"></i></a>-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-trash"></i></a>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">-->
        <!--                        <p style="padding-bottom: 5px;">Wykonuj scenę dalej, jeśli temperatura jest</p>-->
        <!--                        <div class="input-group">-->
        <!--                            <span class="input-group-btn">-->
        <!--                                <button class="btn btn-white">mniejsza niż</button>-->
        <!--                            </span>-->
        <!--                            <input type="text"-->
        <!--                                class="form-control">-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item">-->
        <!--                <div class="timeline-badge action">-->
        <!--                    <function-icon :model="{id: 200}"-->
        <!--                        width="38"></function-icon>-->
        <!--                </div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Dyskoteka ID999</h4>-->
        <!--                        <div class="timeline-panel-controls">-->
        <!--                            <div class="controls">-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-move"></i></a>-->
        <!--                                <a href="#"><i class="glyphicon glyphicon-trash"></i></a>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">-->
        <!--                        <div class="form-group">-->
        <!--                            <div class="btn-group">-->
        <!--                                <button class="btn btn-white">Wyłącz</button>-->
        <!--                                <button class="btn btn-white">Włącz</button>-->
        <!--                                <button class="btn btn-white">Przełącz</button>-->
        <!--                                <button class="btn btn-green">Ustaw parametry</button>-->
        <!--                            </div>-->
        <!--                        </div>-->
        <!--                        <div class="clearfix">-->
        <!--                            <rgbw-parameters-setter :channel-function="{name: 'DIMMERANDRGBLIGHTING'}"></rgbw-parameters-setter>-->
        <!--                        </div>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </li>-->
        <!--            <li class="timeline-item"-->
        <!--                style="margin-top: 60px; opacity: .8">-->
        <!--                <div class="timeline-badge"><i class="glyphicon glyphicon-plus"></i></div>-->
        <!--                <div class="timeline-panel">-->
        <!--                    <div class="timeline-heading">-->
        <!--                        <h4 class="timeline-title">Dodaj element do sceny</h4>-->
        <!--                        &lt;!&ndash;                        <div class="timeline-panel-controls">&ndash;&gt;-->
        <!--                        &lt;!&ndash;                            <div class="controls">&ndash;&gt;-->
        <!--                        &lt;!&ndash;                                <a href="#">&ndash;&gt;-->
        <!--                        &lt;!&ndash;                                    <i class="glyphicon glyphicon-pencil"></i>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                                </a><a href="#">&ndash;&gt;-->
        <!--                        &lt;!&ndash;                                <i class="glyphicon glyphicon-trash"></i>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                            </a>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                            </div>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                            <div class="timestamp">&ndash;&gt;-->
        <!--                        &lt;!&ndash;                                <small class="text-muted">24. Sep 17:03</small>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                            </div>&ndash;&gt;-->
        <!--                        &lt;!&ndash;                        </div>&ndash;&gt;-->
        <!--                    </div>-->
        <!--                    <div class="timeline-body">-->
        <!--                        <button class="btn btn-white">-->
        <!--                            <span class="pe-7s-rocket"></span>-->
        <!--                            Akcja-->
        <!--                        </button>-->
        <!--                        <button class="btn btn-white">-->
        <!--                            <span class="pe-7s-stopwatch"></span>-->
        <!--                            Opóźnienie-->
        <!--                        </button>-->
        <!--                        <button class="btn btn-white">-->
        <!--                            <span class="pe-7s-help1"></span>-->
        <!--                            Warunek-->
        <!--                        </button>-->
        <!--                    </div>-->
        <!--                </div>-->

        <!--            </li>-->
        <!--        </ol>-->
        <!--        <div class="form-group">-->
        <!--            <label>{{ $t('Choose item to use in scene') }}</label>-->
        <!--            <subject-dropdown @input="addSceneOperation($event)"-->
        <!--                channelsDropdownParams="io=output"></subject-dropdown>-->
        <!--        </div>-->
    </div>
</template>

<script>
    import SubjectDropdown from "../devices/subject-dropdown";
    import FunctionIcon from "../channels/function-icon";
    import {channelTitle} from "../common/filters";
    import ChannelActionChooser from "../channels/action/channel-action-chooser";
    import RgbwParametersSetter from "../channels/action/rgbw-parameters-setter";
    import SceneOperationAction from "./scene-operation-action";
    import draggable from 'vuedraggable';
    import SceneOperationDelaySlider from "./scene-operation-delay-slider";

    export default {
        props: ['scene'],
        components: {
            SceneOperationDelaySlider,
            SceneOperationAction, RgbwParametersSetter, ChannelActionChooser, FunctionIcon, SubjectDropdown, draggable
        },
        data() {
            return {
                dragging: false,
                operations: []
            };
        },
        mounted() {
            if (!this.scene.operations) {
                this.$set(this.scene, 'operations', []);
            }
            for (const operation of this.scene.operations) {
                if (operation.delayMs) {
                    this.operations.push({id: Math.random(), delayMs: operation.delayMs});
                }
                this.operations.push(operation);
            }
            console.log(this.operations);
        },
        methods: {
            addSceneOperation({subject, type}) {
                this.scene.operations.push({subject, subjectType: type, delay: 0});
            },
            channelTitle(subject) {
                return channelTitle(subject, this, true);
            },
            possibleActionFilter(subject) {
                return (possibleAction) =>
                    (possibleAction.name != 'OPEN' || subject.function.possibleActions.length == 1) && possibleAction.name != 'CLOSE';
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    ol.scene-timeline {
        /* list-style-type: none;
         margin-left: 20px;
         border-left: 2px solid $supla-grey-light;
         padding: 20px 0;
         li {
             display: flex;
             .icon {
                 display: block;
                 background: $supla-green;
                 width: 50px;
                 height: 50px;
                 border-radius: 50%;
                 font-size: 38px;
                 padding-top: 5px;
                 text-align: center;
                 margin-left: -25px;
             }
         }*/
    }

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
                font-size: 1.4em;
                height: 50px;
                left: 50px;
                line-height: 52px;
                margin-left: -25px;
                position: absolute;
                text-align: center;
                top: 16px;
                width: 50px;
                z-index: 100;
                cursor: move;
                cursor: -webkit-grabbing;

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
                    border-left: 0 solid #ccc;
                    border-right: 15px solid #ccc;
                    border-top: 15px solid transparent;
                    content: " ";
                    display: inline-block;
                    position: absolute;
                    left: -15px;
                    right: auto;
                    top: 26px;
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

<template>
  <div>
    <TransitionGroup name="list" tag="div" class="scene-timeline">
      <div v-for="operation of operations" :key="operation.id" :class="['timeline-item', {'timeline-item-delay': !operation.subject}]">
        <template v-if="operation.subject && operation.subjectType !== 'notification'">
          <div class="timeline-info">
            <a :class="{invisible: !canMoveUp(operation)}" @click="moveOperation(operation, -1)">
              <fa :icon="faChevronUp()" />
            </a>
            <div
              v-tooltip="!(operation.action && operation.action.id) ? $t('choose the action') : ''"
              :class="['timeline-badge action', {warning: !isOperationValid(operation)}]"
            >
              <function-icon :model="operation.subject" width="38"></function-icon>
            </div>
            <a :class="{invisible: !canMoveDown(operation)}" @click="moveOperation(operation, 1)">
              <fa :icon="faChevronDown()" />
            </a>
          </div>
          <div :class="['timeline-panel', {'timeline-panel-danger': displayValidationErrors && !isOperationValid(operation)}]">
            <div class="timeline-heading">
              <h4 class="timeline-title">
                {{ channelTitle(operation.subject) }}
              </h4>
              <div class="timeline-panel-controls">
                <div class="controls">
                  <a @click="deleteOperation(operation)">
                    <fa :icon="faTrash()" />
                  </a>
                </div>
              </div>
            </div>
            <div class="timeline-body">
              <channel-action-chooser
                v-model="operation.action"
                :subject="operation.subject"
                :always-select-first-action="true"
                :possible-action-filter="possibleActionFilter(operation.subject)"
                @input="updateModel((operation.action = $event))"
              >
              </channel-action-chooser>
              <div v-if="waitForCompletionAvailable(operation)" class="mt-2">
                <label class="checkbox2 text-left">
                  <input v-model="operation.waitForCompletion" type="checkbox" @change="updateModel()" />
                  {{
                    $t('Wait for this scene to finish before proceeding to the next step ({time}).', {
                      time: prettyMilliseconds(operation.subject.estimatedExecutionTime),
                    })
                  }}
                </label>
              </div>
            </div>
          </div>
        </template>
        <template v-else-if="operation.subject && operation.subjectType === 'notification'">
          <div class="timeline-info">
            <a :class="{invisible: !canMoveUp(operation)}" @click="moveOperation(operation, -1)">
              <fa :icon="faChevronUp()" />
            </a>
            <div :class="['timeline-badge action', {warning: !isOperationValid(operation)}]">
              <i class="pe-7s-volume"></i>
            </div>
            <a :class="{invisible: !canMoveDown(operation)}" @click="moveOperation(operation, 1)">
              <fa :icon="faChevronDown()" />
            </a>
          </div>
          <div :class="['timeline-panel', {'timeline-panel-danger': displayValidationErrors && !isOperationValid(operation)}]">
            <div class="timeline-heading">
              <h4 class="timeline-title">{{ $t('Send a notification') }}</h4>
              <div class="timeline-panel-controls">
                <div class="controls">
                  <a @click="deleteOperation(operation)">
                    <fa :icon="faTrash()" />
                  </a>
                </div>
              </div>
            </div>
            <div class="timeline-body">
              <NotificationForm v-model="operation.action.param" :display-validation-errors="displayValidationErrors" @input="updateModel()" />
            </div>
          </div>
        </template>
        <template v-else>
          <div class="timeline-info">
            <a :class="{invisible: !canMoveUp(operation)}" @click="moveOperation(operation, -1)">
              <fa :icon="faChevronUp()" />
            </a>
            <div class="timeline-badge"><i class="pe-7s-stopwatch"></i></div>
            <a :class="{invisible: !canMoveDown(operation)}" @click="moveOperation(operation, 1)">
              <fa :icon="faChevronDown()" />
            </a>
          </div>
          <div class="timeline-panel-container">
            <scene-operation-delay-slider
              v-model="operation.delayMs"
              @delete="deleteOperation(operation)"
              @update:model-value="updateModel()"
            ></scene-operation-delay-slider>
          </div>
        </template>
      </div>
    </TransitionGroup>
    <div class="scene-timeline">
      <div class="timeline-item timeline-item-new">
        <div class="timeline-info pt-3">
          <div class="timeline-badge"><i class="pe-7s-plus"></i></div>
        </div>
        <div class="timeline-panel">
          <div class="timeline-body">
            <div class="form-group">
              <label>{{ $t('Add a new action to the scene') }}</label>
              <subject-dropdown
                clear-on-select
                disable-notifications
                channels-dropdown-params="io=output"
                @input="addSceneOperation($event)"
              ></subject-dropdown>
            </div>
            <div class="form-group">
              <label>{{ $t('or another element') }}</label>
              <div>
                <button type="button" class="btn btn-default mr-2" @click="addDelay()">
                  <i class="pe-7s-stopwatch"></i>
                  {{ $t('Add a delay') }}
                </button>
                <button v-if="notificationsEnabled" type="button" class="btn btn-default" @click="addNotification()">
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
  import SubjectDropdown from '../devices/subject-dropdown.vue';
  import FunctionIcon from '../channels/function-icon.vue';
  import {channelTitle, prettyMilliseconds} from '../common/filters';
  import ChannelActionChooser from '../channels/action/channel-action-chooser.vue';
  import SceneOperationDelaySlider from './scene-operation-delay-slider.vue';
  import ChannelFunctionAction from '../common/enums/channel-function-action';
  import ActionableSubjectType from '../common/enums/actionable-subject-type';
  import NotificationForm from '@/notifications/notification-form.vue';
  import {mapState} from 'pinia';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {faChevronDown, faChevronUp, faTrash} from '@fortawesome/free-solid-svg-icons';
  import {useDebounceFn} from '@vueuse/core';

  let UNIQUE_OPERATION_ID = 0;

  export default {
    components: {
      SceneOperationDelaySlider,
      NotificationForm,
      ChannelActionChooser,
      FunctionIcon,
      SubjectDropdown,
    },
    props: {
      value: Array,
      displayValidationErrors: Boolean,
    },
    data() {
      return {
        lastValue: undefined,
        operations: [],
      };
    },
    mounted() {
      this.buildOperations();
    },
    methods: {
      faTrash() {
        return faTrash;
      },
      faChevronDown() {
        return faChevronDown;
      },
      faChevronUp() {
        return faChevronUp;
      },
      buildOperations() {
        if (this.value != this.lastValue) {
          this.operations = [];
          for (const op of this.value || []) {
            const operation = {...op};
            operation.action = {id: operation.actionId, param: operation.actionParam};
            delete operation.actionId;
            delete operation.actionParam;
            operation.id = UNIQUE_OPERATION_ID++;
            if (operation.subject && !operation.subjectType) {
              operation.subjectType = operation.subject.ownSubjectType;
              operation.subjectId = operation.subject.id;
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
          this.operations.push({
            id: UNIQUE_OPERATION_ID++,
            subject,
            subjectType: subject.ownSubjectType,
            delayMs: 0,
          });
        }
      },
      channelTitle(subject) {
        return channelTitle(subject);
      },
      possibleActionFilter() {
        return () => true;
      },
      canMoveUp(operation) {
        const index = this.operations.indexOf(operation);
        return index > 0;
      },
      canMoveDown(operation) {
        const index = this.operations.indexOf(operation);
        return index < this.operations.length - 1;
      },
      moveOperation(operation, change) {
        const index = this.operations.indexOf(operation);
        this.operations.splice(index, 1);
        this.operations.splice(index + change, 0, operation);
        this.updateModel();
      },
      updateModel: useDebounceFn(function () {
        const operations = [];
        let delay = 0;
        for (const op of this.operations) {
          const operation = {...op};
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
      }),
      isOperationValid(operation) {
        return (
          !operation.subjectType ||
          (!!operation.actionId && ChannelFunctionAction.paramsValid(operation.actionId, operation.actionParam)) ||
          (!!operation.action && ChannelFunctionAction.paramsValid(operation.action.id, operation.action.param))
        );
      },
      addDelay() {
        this.operations.push({id: UNIQUE_OPERATION_ID++, delayMs: 5000});
        this.updateModel();
      },
      addNotification() {
        this.operations.push({
          id: UNIQUE_OPERATION_ID++,
          subject: {},
          subjectType: ActionableSubjectType.NOTIFICATION,
          action: {id: ChannelFunctionAction.SEND, param: {}},
        });
        this.updateModel();
      },
      waitForCompletionAvailable(operation) {
        return (
          operation.subjectType === ActionableSubjectType.SCENE &&
          operation.action &&
          [ChannelFunctionAction.EXECUTE, ChannelFunctionAction.INTERRUPT_AND_EXECUTE].includes(operation.action.id)
        );
      },
      prettyMilliseconds(ms) {
        return prettyMilliseconds(ms);
      },
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
      },
    },
  };
</script>

<style lang="scss">
  @use '../styles/variables' as *;

  .scene-timeline {
    list-style: none;
    padding: 20px 0 20px;
    position: relative;

    &:before {
      background-color: #eee;
      bottom: 0;
      content: ' ';
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
        content: ' ';
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

      .timeline-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: absolute;
        left: 25px;
        top: 0;
        z-index: 100;
        > a {
          color: $supla-grey-dark;
          &:hover {
            color: $supla-green;
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
        text-align: center;
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

      .timeline-info + .timeline-panel {
        &:before {
          border-bottom: 15px solid transparent;
          border-left: 0;
          border-right: 15px solid #ccc;
          border-top: 15px solid transparent;
          content: ' ';
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
          content: ' ';
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
    > p,
    > ul {
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
      opacity: 0.5;
    }
  }

  .list-enter-active,
  .list-leave-active {
    transition: all 0.5s ease;
  }

  .list-enter-from,
  .list-leave-to {
    opacity: 0;
    transform: translateX(30px);
  }

  .list-move, /* apply transition to moving elements */
  .list-enter-active,
  .list-leave-active {
    transition: all 0.5s ease;
  }

  .list-enter-from,
  .list-leave-to {
    opacity: 0;
    transform: translateX(30px);
  }

  /* ensure leaving items are taken out of layout flow so that moving
     animations can be calculated correctly. */
  .list-leave-active {
    position: absolute;
  }
</style>

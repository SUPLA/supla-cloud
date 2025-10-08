<template>
  <div>
    <div class="panel-group panel-accordion m-0">
      <div v-for="(possibleCondition, $index) in possibleConditions" :key="$index">
        <div :class="['panel panel-default', {'panel-success': isSelected(possibleCondition)}]">
          <div v-if="possibleConditions.length > 1" class="panel-heading d-flex" @click="changeCondition(possibleCondition)">
            <a role="button" tabindex="0" class="text-inherit flex-grow-1" @keydown.enter.stop="changeCondition(possibleCondition)">
              {{ $t(possibleCondition.caption(subject)) }}
            </a>
            <div>
              <fa v-if="isSelected(possibleCondition)" :icon="faCheck()" />
            </div>
          </div>
          <div v-if="isSelected(possibleCondition) && possibleCondition.component">
            <transition-expand>
              <div v-if="isSelected(possibleCondition)" class="panel-body">
                <Component :is="possibleCondition.component" v-model="currentConditionJson" :subject="subject" v-bind="possibleCondition.props || {}" />
              </div>
            </transition-expand>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {findTriggerDefinition, getTriggerDefinitionsForChannel} from '@/channels/reactions/channel-function-triggers';
  import {faCheck} from '@fortawesome/free-solid-svg-icons';

  export default {
    components: {TransitionExpand},
    props: {
      subject: Object,
      value: Object,
    },
    data() {
      return {
        currentCondition: undefined,
      };
    },
    computed: {
      currentConditionJson: {
        get() {
          return this.value;
        },
        set(def) {
          this.$emit('input', def);
        },
      },
      possibleConditions() {
        return getTriggerDefinitionsForChannel(this.subject);
      },
    },
    watch: {
      value() {
        if (this.value) {
          this.currentCondition = findTriggerDefinition(this.subject, this.value);
        }
      },
    },
    beforeMount() {
      if (!this.currentCondition && this.value) {
        this.currentCondition = findTriggerDefinition(this.subject, this.value);
      }
      if (!this.currentCondition && this.possibleConditions.length === 1) {
        this.changeCondition(this.possibleConditions[0]);
      }
    },
    methods: {
      faCheck() {
        return faCheck;
      },
      isSelected(condition) {
        return condition && condition.caption === this.currentCondition?.caption;
      },
      changeCondition(condition) {
        if (this.possibleConditions.length > 1 || !this.currentCondition) {
          this.currentCondition = condition;
          this.currentConditionJson = this.currentCondition?.def ? this.currentCondition.def() : undefined;
        }
      },
    },
  };
</script>

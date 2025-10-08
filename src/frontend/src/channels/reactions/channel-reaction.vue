<template>
  <BreadcrumbList :current="item.id ? reactionTriggerCaption(item) : $t('New reaction')">
    <router-link :to="{name: 'channel.reactions', params: {id: item.channelId}}">{{ $t('Reactions') }} </router-link>
  </BreadcrumbList>
  <PendingChangesPage
    :header="item.id ? $t('Edit reaction') : $t('New reaction')"
    :is-pending="hasPendingChanges"
    dont-set-page-title
    :deletable="!!item.id"
    :cancellable="!!item.id"
    @delete="deleteConfirm = true"
    @cancel="cancelChanges()"
    @save="submitForm()"
  >
    <div class="channel-reaction row mt-3">
      <div class="col-sm-6">
        <transition-expand>
          <div v-if="displayValidationErrors && !trigger" class="alert alert-danger">
            {{ $t('Please select a condition') }}
          </div>
        </transition-expand>
        <ChannelReactionConditionChooser v-model="trigger" :subject="owningChannel" class="mb-3" @input="onChanged()" />
        <div class="or-hr">{{ $t('THEN') }}</div>
        <transition-expand>
          <div v-if="displayValidationErrors && (!action || !targetSubject)" class="alert alert-danger">
            {{ $t('Please select and configure the action') }}
          </div>
        </transition-expand>
        <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1">
          <div v-if="targetSubject" class="mt-3">
            <ChannelActionChooser
              v-model="action"
              :subject="targetSubject"
              :always-select-first-action="true"
              :context-subject="owningChannel"
              @input="onChanged()"
            />
          </div>
        </SubjectDropdown>
      </div>
      <div class="col-sm-6">
        <div class="details-page-block">
          <h3 class="text-center">{{ $t('Settings') }}</h3>
          <div class="hover-editable text-left">
            <dl>
              <dd>{{ $t('Enabled') }}</dd>
              <dt>
                <toggler v-model="enabled" @update:model-value="onChanged()" />
              </dt>
            </dl>
          </div>
        </div>
        <div class="details-page-block">
          <ActivityConditionsForm v-model="activityConditions" @input="onChanged()" />
        </div>
      </div>
    </div>
    <modal-confirm
      v-if="deleteConfirm"
      class="modal-warning"
      :header="$t('Are you sure you want to delete this reaction?')"
      :loading="loading"
      @confirm="deleteReaction()"
      @cancel="deleteConfirm = false"
    >
    </modal-confirm>
  </PendingChangesPage>
</template>

<script>
  import ChannelReactionConditionChooser from '@/channels/reactions/channel-reaction-condition-chooser.vue';
  import SubjectDropdown from '@/devices/subject-dropdown.vue';
  import ChannelActionChooser from '@/channels/action/channel-action-chooser.vue';
  import ActionableSubjectType from '@/common/enums/actionable-subject-type';
  import {successNotification} from '@/common/notifier';
  import PendingChangesPage from '@/common/pages/pending-changes-page.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {deepCopy} from '@/common/utils';
  import EventBus from '@/common/event-bus';
  import ActivityConditionsForm from '@/activity/activity-conditions-form.vue';
  import {api} from '@/api/api.js';
  import Toggler from '@/common/gui/toggler.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {reactionTriggerCaption} from '@/channels/reactions/channel-function-triggers.js';

  export default {
    components: {
      BreadcrumbList,
      ModalConfirm,
      Toggler,
      ActivityConditionsForm,
      TransitionExpand,
      PendingChangesPage,
      ChannelActionChooser,
      SubjectDropdown,
      ChannelReactionConditionChooser,
    },
    props: {
      item: Object,
    },
    data() {
      return {
        trigger: undefined,
        targetSubject: undefined,
        action: undefined,
        enabled: undefined,
        hasPendingChanges: false,
        deleteConfirm: false,
        loading: false,
        displayValidationErrors: false,
        activityConditions: {},
        showConditionsHelp: false,
      };
    },
    computed: {
      subjectCaption() {
        if (this.targetSubject.ownSubjectType === ActionableSubjectType.NOTIFICATION) {
          return '';
        }
        return this.targetSubject.caption || `ID${this.targetSubject.id} ${this.$t(this.targetSubject.function.caption)}`;
      },
      owningChannel() {
        return this.item?.owningChannel;
      },
      reaction() {
        return {
          trigger: this.trigger,
          subjectId: this.targetSubject?.id,
          subjectType: this.targetSubject?.ownSubjectType,
          actionId: this.action?.id,
          actionParam: this.action?.param,
          enabled: this.enabled,
          isValid: !!(this.trigger && this.action && this.targetSubject),
          ...this.activityConditions,
        };
      },
    },
    beforeMount() {
      this.initFromItem();
    },
    methods: {
      reactionTriggerCaption,
      onChanged() {
        this.hasPendingChanges = true;
      },
      cancelChanges() {
        this.initFromItem();
      },
      initFromItem() {
        this.displayValidationErrors = false;
        const item = deepCopy(this.item);
        this.trigger = item.trigger || undefined;
        this.targetSubject = item.subject;
        this.action = item.actionId ? {id: item.actionId, param: item.actionParam} : undefined;
        this.enabled = item.enabled;
        this.activityConditions = {
          activeFrom: item.activeFrom,
          activeTo: item.activeTo,
          activeHours: item.activeHours,
          activityConditions: item.activityConditions,
        };
        this.$nextTick(() => (this.hasPendingChanges = false));
      },
      submitForm() {
        this.displayValidationErrors = true;
        if (this.reaction.isValid) {
          const updateFunc = this.item.id ? 'updateReaction' : 'addNewReaction';
          this.loading = true;
          this[updateFunc]()
            .then(() => (this.hasPendingChanges = false))
            .then(() => (this.displayValidationErrors = false))
            .finally(() => (this.loading = false));
        }
      },
      addNewReaction() {
        return api.post(`channels/${this.owningChannel.id}/reactions?include=subject,owningChannel`, this.reaction).then((response) => {
          this.$emit('add', response.body);
          EventBus.$emit('channel-updated');
          successNotification(this.$t('Success'), this.$t('The reaction has been added'));
        });
      },
      updateReaction() {
        return api.put(`channels/${this.owningChannel.id}/reactions/${this.item.id}?include=subject,owningChannel`, this.reaction).then((response) => {
          this.$emit('update', response.body);
          successNotification(this.$t('Success'), this.$t('The reaction has been changed'));
        });
      },
      deleteReaction() {
        this.loading = true;
        api
          .delete_(`channels/${this.owningChannel.id}/reactions/${this.item.id}`)
          .then(() => this.$emit('delete'))
          .then(() => EventBus.$emit('channel-updated'))
          .finally(() => (this.loading = false));
      },
    },
  };
</script>

<style lang="scss" scoped>
  .step-link {
    font-size: 1.3em;
    text-align: center;
    display: block;
  }
</style>

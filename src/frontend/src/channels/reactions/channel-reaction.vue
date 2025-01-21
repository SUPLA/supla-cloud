<template>
    <PendingChangesPage
        :header="item.id ? $t('Edit reaction') : $t('New reaction')" :is-pending="hasPendingChanges"
        dont-set-page-title
        :deletable="!!item.id" :cancellable="!!item.id"
        @delete="deleteConfirm = true"
        @cancel="cancelChanges()"
        @save="submitForm()">
        <div class="channel-reaction row mt-3">
            <div class="col-sm-6">
                <transition-expand>
                    <div class="alert alert-danger" v-if="displayValidationErrors && !trigger">
                        {{ $t('Please select a condition') }}
                    </div>
                </transition-expand>
                <ChannelReactionConditionChooser :subject="owningChannel" v-model="trigger" @input="onChanged()" class="mb-3"/>
                <div class="or-hr">{{ $t('THEN') }}</div>
                <transition-expand>
                    <div class="alert alert-danger" v-if="displayValidationErrors && (!action || !targetSubject)">
                        {{ $t('Please select and configure the action') }}
                    </div>
                </transition-expand>
                <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1">
                    <div v-if="targetSubject" class="mt-3">
                        <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action"
                            @input="onChanged()" :contextSubject="owningChannel"/>
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
                                <toggler v-model="enabled" @input="onChanged()"/>
                            </dt>
                        </dl>
                    </div>
                </div>
                <div class="details-page-block">
                    <h3 class="text-center">
                        {{ $t('Activity conditions') }}
                        <a @click="showConditionsHelp = !showConditionsHelp">
                            <fa icon="info-circle" class="ml-2 small"/>
                        </a>
                    </h3>
                    <transition-expand>
                        <div v-if="showConditionsHelp" class="alert alert-info">
                            {{ $t('The reaction will be active when all of the conditions will be meet. If you choose to set all of the available time settings, the reaction will be active when the time is between active from and active to, is within the selected working schedule and meets one of the daytime criteria.') }}
                        </div>
                    </transition-expand>
                    <DateRangePicker v-model="activeDateRange"
                        :label-date-start="$t('Active from')"
                        :label-date-end="$t('Active to')"
                        @input="onChanged()"/>
                    <div class="form-group text-center">
                        <label>
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="useWorkingSchedule" @change="onChanged()">
                                {{ $t('Use working schedule for this reaction') }}
                            </label>
                        </label>
                    </div>
                    <transition-expand>
                        <week-schedule-selector v-if="useWorkingSchedule" class="narrow mode-1-green"
                            v-model="activeHours"
                            @input="onChanged()"></week-schedule-selector>
                    </transition-expand>
                    <DaytimeActivityConditions class="mt-4" v-model="activityConditions" @input="onChanged()"/>
                </div>
            </div>
        </div>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteReaction()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this reaction?')"
            :loading="loading">
        </modal-confirm>
    </PendingChangesPage>
</template>

<script>
    import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
    import SubjectDropdown from "@/devices/subject-dropdown.vue";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import {successNotification} from "@/common/notifier";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {deepCopy} from "@/common/utils";
    import EventBus from "@/common/event-bus";
    import DateRangePicker from "@/direct-links/date-range-picker.vue";
    import WeekScheduleSelector from "@/activity/week-schedule-selector.vue";
    import {mapValues, pickBy} from "lodash";
    import DaytimeActivityConditions from "@/activity/daytime-activity-conditions.vue";

    export default {
        components: {
            DaytimeActivityConditions,
            WeekScheduleSelector,
            DateRangePicker,
            TransitionExpand, PendingChangesPage, ChannelActionChooser, SubjectDropdown, ChannelReactionConditionChooser
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
                activeDateRange: {},
                useWorkingSchedule: false,
                activeHours: {},
                activityConditions: [],
                showConditionsHelp: false,
            };
        },
        beforeMount() {
            this.initFromItem();
        },
        methods: {
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
                this.activeDateRange = {dateStart: item.activeFrom, dateEnd: item.activeTo};
                if (item.activeHours) {
                    this.activeHours = mapValues(item.activeHours, (hours) => {
                        const hoursDef = {};
                        [...Array(24).keys()].forEach((hour) => hoursDef[hour] = hours.includes(hour) ? 1 : 0);
                        return hoursDef;
                    });
                    this.useWorkingSchedule = true;
                } else {
                    this.activeHours = {};
                    this.useWorkingSchedule = false;
                }
                this.activityConditions = item.activityConditions;
                this.$nextTick(() => this.hasPendingChanges = false);
            },
            submitForm() {
                this.displayValidationErrors = true;
                if (this.reaction.isValid) {
                    const updateFunc = this.item.id ? 'updateReaction' : 'addNewReaction';
                    this.loading = true;
                    this[updateFunc]()
                        .then(() => this.hasPendingChanges = false)
                        .then(() => this.displayValidationErrors = false)
                        .finally(() => this.loading = false);
                }
            },
            addNewReaction() {
                return this.$http.post(`channels/${this.owningChannel.id}/reactions?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('add', response.body);
                        EventBus.$emit('channel-updated');
                        successNotification(this.$t('Success'), this.$t('The reaction has been added'));
                    });
            },
            updateReaction() {
                return this.$http.put(`channels/${this.owningChannel.id}/reactions/${this.item.id}?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('update', response.body);
                        successNotification(this.$t('Success'), this.$t('The reaction has been changed'));
                    });
            },
            deleteReaction() {
                this.loading = true;
                this.$http.delete(`channels/${this.owningChannel.id}/reactions/${this.item.id}`)
                    .then(() => this.$emit('delete'))
                    .then(() => EventBus.$emit('channel-updated'))
                    .finally(() => this.loading = false);
            }
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
                    activeFrom: this.activeDateRange.dateStart || null,
                    activeTo: this.activeDateRange.dateEnd || null,
                    activeHours: this.useWorkingSchedule ? mapValues(this.activeHours, (hours) => {
                        return Object.keys(pickBy(hours, (selection) => !!selection)).map((hour) => parseInt(hour));
                    }) : null,
                    activityConditions: this.activityConditions,
                };
            },
        },
    }
</script>

<style lang="scss" scoped>
    .step-link {
        font-size: 1.3em;
        text-align: center;
        display: block;
    }
</style>

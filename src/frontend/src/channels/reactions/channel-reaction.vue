<template>
    <PendingChangesPage :header="item.id ? $t('Edit reaction') : $t('New reaction')" :is-pending="hasPendingChanges" :deletable="!!item.id"
        @delete="deleteConfirm = true"
        @cancel="cancelChanges()"
        @save="submitForm()">
        <div class="channel-reaction row mt-3">
            <div class="col-sm-6">
                <ChannelReactionConditionChooser :subject="owningChannel" v-model="trigger" @input="onChanged()"/>
            </div>
            <div class="col-sm-6">
                <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1"/>
                <div v-if="targetSubject">
                    <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action"
                        @input="onChanged()"/>
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
    import {triggerHumanizer} from "@/channels/reactions/trigger-humanizer";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import {successNotification} from "@/common/notifier";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";

    export default {
        components: {PendingChangesPage, ChannelActionChooser, SubjectDropdown, ChannelReactionConditionChooser},
        props: {
            item: Object,
        },
        data() {
            return {
                trigger: {},
                targetSubject: undefined,
                action: undefined,
                hasPendingChanges: false,
                deleteConfirm: false,
                loading: false,
            };
        },
        mounted() {
            if (this.item.id) {
                this.initFromItem();
            }
        },
        methods: {
            onChanged() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.initFromItem();
            },
            initFromItem() {
                this.hasPendingChanges = false;
                this.trigger = this.item.trigger ? {...this.item.trigger} : {};
                this.targetSubject = this.item.subject ? {...this.item.subject} : undefined;
                this.action = this.item.actionId ? {id: this.item.actionId, param: {...this.item.actionParam}} : undefined;
            },
            submitForm() {
                const updateFunc = this.item.id ? 'updateReaction' : 'addNewReaction';
                this.loading = true;
                this[updateFunc]()
                    .then(() => this.hasPendingChanges = false)
                    .finally(() => this.loading = false);
            },
            addNewReaction() {
                return this.$http.post(`channels/${this.owningChannel.id}/reactions?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('add', response.body);
                        successNotification(this.$t('Success'), this.$t('Reakcja została dodana'));
                    });
            },
            updateReaction() {
                return this.$http.put(`channels/${this.owningChannel.id}/reactions/${this.item.id}?include=subject,owningChannel`, this.reaction)
                    .then((response) => {
                        this.$emit('update', response.body);
                        successNotification(this.$t('Success'), this.$t('Reakcja została zaktualizowana'));
                    });
            },
            deleteReaction() {
                this.loading = true;
                this.$http.delete(`channels/${this.owningChannel.id}/reactions/${this.item.id}`)
                    .then(() => this.$emit('delete'))
                    .finally(() => this.loading = false);
            }
        },
        computed: {
            triggerCaption() {
                return triggerHumanizer(this.subject.functionId, this.trigger, this);
            },
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
                    isValid: !!(this.trigger && this.action),
                };
            },
        }
    }
</script>

<style lang="scss" scoped>
    .step-link {
        font-size: 1.3em;
        text-align: center;
        display: block;
    }
</style>

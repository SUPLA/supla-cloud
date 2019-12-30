<template>
    <div>
        <dl>
            <dd>{{ $t('On trigger') }}</dd>
            <dt>
                <div class="">
                    <div class="dropdown hovered">
                        <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                            type="button"
                            data-toggle="dropdown">
                            <h4>
                                <span v-if="selectedBehavior">{{ $t('actionTriggerBehavior_' + selectedBehavior) }}</span>
                                <span v-else>{{ $t('Select trigger') }}</span>
                            </h4>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li v-for="behavior in channel.config.supportedBehaviors">
                                <a @click="onBehaviorChange(behavior)"
                                    v-show="behavior !== selectedBehavior">{{ $t('actionTriggerBehavior_' + behavior) }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </dt>
        </dl>
        <dl v-if="selectedBehavior">
            <dd>{{ $t('Do / execute') }}</dd>
            <dt>
                <subject-dropdown v-model="behaviorSubject"
                    @input="onBehaviorSubjectChange()"
                    channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>
                <div v-if="behaviorSubject">
                    <channel-action-chooser :subject="behaviorSubject"
                        @input="onBehaviorActionChange()"
                        v-model="behaviorAction"></channel-action-chooser>
                </div>
            </dt>
        </dl>


        <!--        <div class="form-group">-->
        <!--            <label>{{ $t('Action when pressed (ON)' )}}</label>-->

        <!--        </div>-->
        <!--        <div class="form-group">-->
        <!--            <label>{{ $t('Action when released (OFF)' )}}</label>-->
        <!--            <subject-dropdown v-model="subject2WithType"-->
        <!--                channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>-->
        <!--            <div v-if="subject2WithType">-->
        <!--                <channel-action-chooser :subject="subject2WithType.subject"-->
        <!--                    v-model="action2"></channel-action-chooser>-->
        <!--            </div>-->
        <!--        </div>-->
    </div>
</template>

<script>
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../action/channel-action-chooser";
    import changeCase from "change-case";

    export default {
        components: {ChannelActionChooser, SubjectDropdown},
        props: ['channel'],
        data() {
            return {
                selectedBehavior: undefined,
                behaviorSubject: undefined,
                behaviorAction: undefined
            };
        },
        mounted() {
            if (!this.channel.config.actions) {
                this.$set(this.channel.config, 'actions', {});
            }
        },
        methods: {
            onBehaviorChange(behavior) {
                this.selectedBehavior = behavior;
                this.behaviorSubject = undefined;
                this.behaviorAction = undefined;
                const currentAction = this.channel.config.actions[behavior];
                if (currentAction) {
                    const endpoint = `${changeCase.paramCase(currentAction.subjectType)}s/${currentAction.subjectId}`;
                    this.$http.get(endpoint)
                        .then(response => this.behaviorSubject = response.body)
                        .then(() => this.behaviorAction = currentAction.action);
                }
            },
            onBehaviorSubjectChange() {
                this.behaviorAction = undefined;
            },
            onBehaviorActionChange() {
                this.channel.config.actions[this.selectedBehavior] = {
                    subjectId: this.behaviorSubject.id,
                    subjectType: this.behaviorSubject.subjectType,
                    action: this.behaviorAction,
                };
            }
        }
    };
</script>

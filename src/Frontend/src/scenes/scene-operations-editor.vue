<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>{{ $t('Subject') }}</th>
                <th>{{ $t('Action') }}</th>
                <th>{{ $t('Delay') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="operation in scene.operations">
                <td>
                    <function-icon :model="operation.subject"
                        width="40"></function-icon>
                    {{ channelTitle(operation.subject) }}
                </td>
                <td>
                    <channel-action-chooser :subject="operation.subject"
                        v-model="operation.action"
                        :possible-action-filter="possibleActionFilter(operation.subject)"></channel-action-chooser>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number"
                            min="0"
                            class="form-control"
                            maxlength="4"
                            v-model="operation.delay">
                        <span class="input-group-addon">s</span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="form-group">
            <label>{{ $t('Choose item to use in scene') }}</label>
            <subject-dropdown @input="addSceneOperation($event)"
                channelsDropdownParams="io=output"></subject-dropdown>
        </div>
    </div>
</template>

<script>
    import SubjectDropdown from "../devices/subject-dropdown";
    import FunctionIcon from "../channels/function-icon";
    import {channelTitle} from "../common/filters";
    import ChannelActionChooser from "../channels/action/channel-action-chooser";

    export default {
        props: ['scene'],
        components: {ChannelActionChooser, FunctionIcon, SubjectDropdown},
        data() {
            return {};
        },
        mounted() {
            if (!this.scene.operations) {
                this.$set(this.scene, 'operations', []);
            }
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
                    (possibleAction.name != 'OPEN' || subject.function.possibleActions.length == 1)
                    && possibleAction.name != 'CLOSE';
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

</style>

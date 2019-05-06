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
            </tr>
            </tbody>
        </table>
        <div class="form-group">
            <label>{{ $t('Choose item to use in scene') }}</label>
            <subject-dropdown @input="addSceneOperation($event)"></subject-dropdown>
        </div>
    </div>
</template>

<script>
    import SubjectDropdown from "../devices/subject-dropdown";
    import FunctionIcon from "../channels/function-icon";
    import {channelTitle} from "../common/filters";

    export default {
        props: ['scene'],
        components: {FunctionIcon, SubjectDropdown},
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
                this.scene.operations.push({subject, subjectType: type});
            },
            channelTitle(subject) {
                return channelTitle(subject, this, true);
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

</style>

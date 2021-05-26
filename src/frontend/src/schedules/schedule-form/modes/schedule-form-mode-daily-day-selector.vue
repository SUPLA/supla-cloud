<template>
    <div>
        <div class="form-group"
            v-if="groups.length > 1">
            <div class="btn-group btn-group-justified">
                <a v-for="(group, index) in groups"
                    @click="currentGroupIndex = index"
                    :key="index"
                    :class="['btn', {'btn-default': index !== currentGroupIndex, 'btn-green': index === currentGroupIndex}]">
                    {{ groupLabel(group) || $t('empty') }}
                </a>
            </div>
        </div>
        <div class="form-group text-center">
            <div class="daily-checkboxes">
                <div class="daily-checkboxes-button">
                    <a class="btn btn-default invisible"
                        v-if="groups.length > 1 || available.length > 0">&lt;</a>
                </div>
                <div class="checkboxes">
                    <div :class="['checkbox-inline', {'disabled': chosen.includes(weekday) && !currentGroup.includes(weekday)}]"
                        :key="weekday"
                        v-for="weekday in [1,2,3,4,5,6,7]">
                        <label>
                            <input type="checkbox"
                                :value="weekday"
                                :disabled="chosen.includes(weekday) && !currentGroup.includes(weekday)"
                                v-model="groups[currentGroupIndex]">
                            {{ dayLabel(weekday) }}
                        </label>
                    </div>
                </div>
                <div class="daily-checkboxes-button">
                    <a class="btn btn-default"
                        @click="nextGroup()"
                        v-if="groups.length > 1 || available.length > 0">&gt;</a>
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 100px;">&nbsp;</div>
        <div class="form-group">&nbsp;</div>
        <div class="form-group">&nbsp;</div>
        <div class="form-group">&nbsp;</div>
    </div>
</template>

<script>

    import moment from "moment";
    import {flatten, difference} from "lodash";

    export default {
        props: ['value', 'current'],
        data() {
            return {
                currentGroupIndex: 0,
                groups: [
                    [1, 2, 3, 4, 5, 6, 7],
                ]
            };
        },
        methods: {
            nextGroup() {
                let emptyIndex = -1;
                while ((emptyIndex = this.groups.findIndex((group) => group.length === 0)) >= 0) {
                    this.groups.splice(emptyIndex, 1);
                    this.currentGroupIndex = -1;
                }
                this.currentGroupIndex += 1;
                if (this.currentGroupIndex >= this.groups.length) {
                    if (this.available.length) {
                        this.groups.push([...this.available]);
                    } else {
                        this.currentGroupIndex = 0;
                    }
                }
            },
            dayLabel(day) {
                return moment(day === 7 ? 0 : day, 'd').format('ddd');
            },
            groupLabel(group) {
                return [...group].sort().map(this.dayLabel).join(', ');
            }
        },
        mounted() {

        },
        computed: {
            chosen() {
                return flatten(this.groups);
            },
            currentGroup() {
                return this.groups[this.currentGroupIndex];
            },
            available() {
                return difference([1, 2, 3, 4, 5, 6, 7], this.chosen);
            }
        }
    };
</script>

<style lang="scss">
    @import '../../../styles/variables';

    .daily-checkboxes {
        display: flex;
        align-items: center;
        .checkboxes {
            flex: 1;
            .checkbox-inline.disabled {
                opacity: .5;
            }
        }
    }
</style>

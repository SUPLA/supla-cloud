<template>
    <div>
        <div class="form-group">
            <div class="btn-group btn-group-flex">
                <a v-for="(group, index) in groups"
                    @click="enterGroup(index)"
                    :key="index"
                    :class="['btn', {'btn-warning': !groupLabel(group),'btn-default': groupLabel(group) && index !== currentGroupIndex, 'btn-green': groupLabel(group) && index === currentGroupIndex}]">
                    <span v-if="groupLabel(group) ">{{ groupLabel(group) }}</span>
                    <em v-else>{{ $t('empty') }}</em>
                </a>
                <a class="btn btn-black btn-no-grow new-group-button"
                    @click="newGroup()"
                    v-if="available.length">
                    <i class="pe-7s-plus"></i>
                </a>
            </div>
        </div>
        <div class="form-group daily-checkboxes">
            <div :class="['checkbox-inline', {'disabled': chosen.includes(weekday) && !currentGroup.includes(weekday)}]"
                :key="weekday"
                v-for="weekday in [1,2,3,4,5,6,7]">
                <label>
                    <input type="checkbox"
                        :value="weekday"
                        @change="updateModel()"
                        :disabled="chosen.includes(weekday) && !currentGroup.includes(weekday)"
                        v-model="groups[currentGroupIndex]">
                    {{ dayLabel(weekday) }}
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    import {difference, flatten} from "lodash";
    import {DateTime} from "luxon";

    export default {
        props: ['weekdayGroups'],
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
                    this.$emit('groupIndexRemove', emptyIndex);
                    if (emptyIndex <= this.currentGroupIndex) {
                        this.currentGroupIndex -= 1;
                    }
                }
                this.currentGroupIndex += 1;
                if (this.currentGroupIndex >= this.groups.length) {
                    if (this.available.length) {
                        this.groups.push([...this.available]);
                    } else {
                        this.currentGroupIndex = 0;
                    }
                }
                this.updateModel();
            },
            newGroup() {
                this.enterGroup(this.groups.length);
            },
            enterGroup(groupIndex) {
                this.currentGroupIndex = groupIndex - 1;
                this.nextGroup();
            },
            dayLabel(day) {
                return DateTime.fromFormat(day.toString(), 'c').toFormat('ccc');
            },
            groupLabel(group) {
                return [...group].sort().map(this.dayLabel).join(', ');
            },
            updateModel() {
                const weekdayGroups = this.groups.map((group) => [...group].sort().join(','));
                this.$emit('groups', weekdayGroups);
                this.$emit('groupIndex', this.currentGroupIndex);
            }
        },
        mounted() {
            if (this.weekdayGroups) {
                this.groups = [];
                this.weekdayGroups.forEach((group) => {
                    if (group === '*') {
                        group = '1,2,3,4,5,6,7';
                    }
                    const newDays = difference(group.split(','), this.chosen).map((day) => parseInt(day));
                    if (newDays.length) {
                        this.groups.push(newDays);
                    }
                });
            }
        },
        computed: {
            chosen() {
                return flatten(this.groups);
            },
            currentGroup() {
                return this.groups[this.currentGroupIndex] || [];
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
        text-align: center;
        .checkbox-inline.disabled {
            opacity: .5;
        }
    }

    .new-group-button {
        font-weight: bold;
    }

    .btn-group-flex {
        display: flex;
        .btn {
            flex-grow: 1;
        }
        .btn.btn-no-grow {
            flex-grow: 0;
        }
    }
</style>
